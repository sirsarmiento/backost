<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Producto;
use App\Entity\Costo\PiezasProducto;
use App\Entity\Costo\Piezas;
use App\Entity\Costo\Activo;
use App\Entity\Costo\Perfil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;
use App\Entity\User;
use App\Service\InventarioService;

/**
 * @method Producto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Producto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Producto[]    findAll()
 * @method Producto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductoRepository extends ServiceEntityRepository
{
    private $security;
    private $inventario;

    public function __construct(ManagerRegistry $registry, Security $security, InventarioService $inventario)
    {
        $this->security = $security;
        $this->inventario = $inventario;
        parent::__construct($registry, Producto::class);
    }

    /**
     * Create Producto con PiezasProducto.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Validar datos requeridos
            $requiredFields = ['nombre', 'medida', 'clasificacion'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Datos incompletos',
                        'error' => "El campo '$field' es requerido"
                    ], 400);
                }
            }

            // Validar que piezas sea un array si existe
            // CORREGIDO: Verificar tanto 'piezas' como 'piezasProducto'
            $piezasData = null;
            if (isset($data['piezas']) && is_array($data['piezas'])) {
                $piezasData = $data['piezas'];
            } elseif (isset($data['piezasProducto']) && is_array($data['piezasProducto'])) {
                $piezasData = $data['piezasProducto'];
            } elseif (isset($data['piezas']) && !is_array($data['piezas'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'error' => 'El campo "piezas" debe ser un array'
                ], 400);
            }

            // Crear entidad principal - Producto
            $entity = new Producto();
            
            // Asignar propiedades básicas
            $entity->setNombre($data['nombre']);
            $entity->setMedida($data['medida']);
            $entity->setClasificacion($data['clasificacion']);
            
            if (isset($data['sku'])) {
                $entity->setSku($data['sku']);
            }
            
            if (isset($data['descripcion'])) {
                $entity->setDescripcion($data['descripcion']);
            }
            
            // Asignar nuevas propiedades
            if (isset($data['tasaFallo'])) {
                $entity->setTasaFallo((float) $data['tasaFallo']);
            } else {
                $entity->setTasaFallo(0.00);
            }
            
            if (isset($data['tiempoSetup'])) {
                $entity->setTiempoSetup((float) $data['tiempoSetup']);
            } else {
                $entity->setTiempoSetup(0.00);
            }
            
            if (isset($data['postProcesado'])) {
                $entity->setPostProcesado((float) $data['postProcesado']);
            } else {
                $entity->setPostProcesado(0.00);
            }
            
            if (isset($data['margenGanancia'])) {
                $entity->setMargenGanancia((float) $data['margenGanancia']);
            } else {
                $entity->setMargenGanancia(0.00);
            }

            // Asignar perfil si existe
            if (isset($data['perfil'])) {
                $perfil = $entityManager->getRepository(Perfil::class)->find($data['perfil']);
                if ($perfil) {
                    $entity->setPerfil($perfil);
                }
            }

            // Obtener usuario actual
            $currentUser = $entityManager->getRepository(User::class)
                ->find($this->security->getUser()->getId());
            
            if (!$currentUser) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            $entity->setCreateBy($currentUser->getUserName());
            $entity->setCreateAt(new \DateTime());
            
            // Asignar empresa
            $empresa = $entityManager->getRepository(Empresa::class)
                ->find($this->security->getUser()->getIdempresa());
            
            if ($empresa) {
                $entity->setEmpresa($empresa);
            }

            // Validar entidad principal
            $errors = $validator->validate($entity);
            if ($errors->count() > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errorMessages
                ], 422);
            }

            // Persistir el producto primero para tener el ID
            $entityManager->persist($entity);
            $entityManager->flush();

            // Procesar piezas si existen
            if ($piezasData && !empty($piezasData)) {
                $this->processPiezasProducto($entity, $piezasData, $entityManager, $validator);
                // Flush para guardar las piezas
                $entityManager->flush();
            }

            $this->inventario->recalcularReservas();
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Producto y piezas creados exitosamente',
                'productoId' => $entity->getId()
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa las piezas del producto
     */
    private function processPiezasProducto($producto, $piezasData, $entityManager, $validator)
    {
        foreach ($piezasData as $piezaData) {
            // Crear nueva PiezasProducto
            $piezaProducto = new PiezasProducto();
            
            // Asignar el producto
            $piezaProducto->setProducto($producto);
            
            // Asignar datos de la pieza
            if (isset($piezaData['nombre'])) {
                $piezaProducto->setNombre($piezaData['nombre']);
            }
            
            if (isset($piezaData['cantidad'])) {
                $piezaProducto->setCantidad((int) $piezaData['cantidad']);
            }
            
            if (isset($piezaData['gramos'])) {
                $piezaProducto->setGramos((float) $piezaData['gramos']);
            }
            
            if (isset($piezaData['metros'])) {
                $piezaProducto->setMetros((float) $piezaData['metros']);
            }
            
            if (isset($piezaData['horas'])) {
                $piezaProducto->setHoras((int) $piezaData['horas']);
            }
            
            if (isset($piezaData['minutos'])) {
                $piezaProducto->setMinutos((int) $piezaData['minutos']);
            }
            
            if (isset($piezaData['precioMaterial'])) {
                $piezaProducto->setPrecioMaterial((float) $piezaData['precioMaterial']);
            }
            
            if (isset($piezaData['tipo'])) {
                $piezaProducto->setTipo($piezaData['tipo']);
            }
            
            // Asignar activo si existe
            if (isset($piezaData['activo']) && $piezaData['activo']) {
                $activo = $entityManager->getRepository(Activo::class)->find($piezaData['activo']);
                if ($activo) {
                    $piezaProducto->setActivo($activo);
                }
            }
            
            // Asignar máquina si existe
            if (isset($piezaData['maquina']) && $piezaData['maquina']) {
                $maquina = $entityManager->getRepository(Activo::class)->find($piezaData['maquina']);
                if ($maquina) {
                    $piezaProducto->setMaquina($maquina);
                }
            }
            
            // Validar la entidad PiezasProducto
            $errors = $validator->validate($piezaProducto);
            if ($errors->count() > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                throw new \Exception('Error de validación en piezas: ' . json_encode($errorMessages));
            }
            
            // Persistir la pieza
            $entityManager->persist($piezaProducto);
        }
    }

    /**
     * Get all productos with their piezas
     */
    public function getAll(): array 
    {
        try {
            $productos = $this->findAll();

            $result = [];

            foreach ($productos as $producto) {
                $piezas = [];
                
                // Verificar si hay piezas y recorrerlas
                if ($producto->getPiezasProducto() && !$producto->getPiezasProducto()->isEmpty()) {
                    foreach ($producto->getPiezasProducto() as $piezasProducto) {
                        $piezaData = [
                            'id' => $piezasProducto->getId(),
                            'nombre' => $piezasProducto->getNombre(),
                            'cantidad' => $piezasProducto->getCantidad(),
                            'gramos' => $piezasProducto->getGramos(),
                            'metros' => $piezasProducto->getMetros(),
                            'horas' => $piezasProducto->getHoras(),
                            'minutos' => $piezasProducto->getMinutos(),
                            'precioMaterial' => $piezasProducto->getPrecioMaterial(),
                            'tipo' => $piezasProducto->getTipo(),
                        ];
                        
                        // Obtener datos del activo relacionado si existe
                        if ($piezasProducto->getActivo()) {
                            $activo = $piezasProducto->getActivo();
                            $piezaData['activo'] = [
                                'id' => $activo->getId(),
                                'nombre' => $activo->getNombre() ?? null,
                            ];
                        }
                        
                        // Obtener datos de la máquina relacionada si existe
                        if ($piezasProducto->getMaquina()) {
                            $maquina = $piezasProducto->getMaquina();
                            $piezaData['maquina'] = [
                                'id' => $maquina->getId(),
                                'nombre' => $maquina->getNombre() ?? null,
                            ];
                        }
                        
                        $piezas[] = $piezaData;
                    }
                }
                
                $result[] = [
                    'id' => $producto->getId(),
                    'nombre' => $producto->getNombre(),
                    'medida' => $producto->getMedida(),
                    'clasificacion' => $producto->getClasificacion(),
                    'descripcion' => $producto->getDescripcion(),
                    'sku' => $producto->getSku(),
                    'perfil' => $producto->getPerfil() ? [
                        'id' => $producto->getPerfil()->getId(),
                        'nombre' => $producto->getPerfil()->getNombre()
                    ] : null,
                    'tasaFallo' => $producto->getTasaFallo(),
                    'tiempoSetup' => $producto->getTiempoSetup(),
                    'postProcesado' => $producto->getPostProcesado(),
                    'margenGanancia' => $producto->getMargenGanancia(),
                    'cantidadStock' => $producto->getCantidadStock(),
                    'empresa' => $producto->getEmpresa() ? $producto->getEmpresa()->getId() : null,
                    'createAt' => $producto->getCreateAt() ? $producto->getCreateAt()->format('Y-m-d H:i:s') : null,
                    'createBy' => $producto->getCreateBy(),
                    'updateAt' => $producto->getUpdateAt() ? $producto->getUpdateAt()->format('Y-m-d H:i:s') : null,
                    'updateBy' => $producto->getUpdateBy(),
                    'piezas' => $piezas
                ];
            }

            return $result;
            
        } catch (\Exception $e) {
            return [
                'error' => 'Error al obtener los productos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update Producto with PiezasProducto.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar el producto existente
            $producto = $this->find($id);
            
            if (!$producto) {
                return new JsonResponse(['success' => false, 'message' => 'Producto no encontrado'], 404);
            }

            // Actualizar propiedades básicas
            if (isset($data['nombre'])) {
                $producto->setNombre($data['nombre']);
            }
            if (isset($data['medida'])) {
                $producto->setMedida($data['medida']);
            }
            if (isset($data['clasificacion'])) {
                $producto->setClasificacion($data['clasificacion']);
            }
            if (isset($data['sku'])) {
                $producto->setSku($data['sku']);
            }
            if (isset($data['descripcion'])) {
                $producto->setDescripcion($data['descripcion']);
            }

            // Actualizar nuevas propiedades
            if (isset($data['tasaFallo'])) {
                $producto->setTasaFallo((float) $data['tasaFallo']);
            }
            
            if (isset($data['tiempoSetup'])) {
                $producto->setTiempoSetup((float) $data['tiempoSetup']);
            }
            
            if (isset($data['postProcesado'])) {
                $producto->setPostProcesado((float) $data['postProcesado']);
            }
            
            if (isset($data['margenGanancia'])) {
                $producto->setMargenGanancia((float) $data['margenGanancia']);
            }

            // Actualizar perfil
            if (isset($data['perfil'])) {
                $perfil = $entityManager->getRepository(Perfil::class)->find($data['perfil']);
                if ($perfil) {
                    $producto->setPerfil($perfil);
                } else {
                    $producto->setPerfil(null);
                }
            }

            // Validar entidad principal
            $errors = $validator->validate($producto);
            if ($errors->count() > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errorMessages
                ], 422);
            }

            // Obtener usuario actual para auditoría
            $currentUser = $entityManager->getRepository(User::class)
                ->find($this->security->getUser()->getId());

            if ($currentUser) {
                $producto->setUpdateBy($currentUser->getUserName());
                $producto->setUpdateAt(new \DateTime());
            }

            // Procesar piezas si existen
            if (isset($data['piezas']) && is_array($data['piezas'])) {
                $this->updatePiezasProducto($producto, $data['piezas'], $entityManager, $validator);
            }
            
            // Persistir y flush
            $entityManager->flush();
            $this->inventario->recalcularReservas();
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'id' => $producto->getId()
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza las piezas asociadas al producto
     */
    private function updatePiezasProducto(Producto $producto, array $piezas, EntityManagerInterface $em, $validator): void
    {
        // Obtener piezas existentes
        $existingPiezas = $producto->getPiezasProducto();
        $existingPiezasMap = [];
        
        foreach ($existingPiezas as $piezaProducto) {
            $existingPiezasMap[$piezaProducto->getId()] = $piezaProducto;
        }

        $piezasToKeep = [];
        
        foreach ($piezas as $piezaData) {
            try {
                $piezaProductoId = $piezaData['id'] ?? null;
                
                if ($piezaProductoId && isset($existingPiezasMap[$piezaProductoId])) {
                    // Actualizar PiezasProducto existente
                    $piezasProducto = $existingPiezasMap[$piezaProductoId];
                    unset($existingPiezasMap[$piezaProductoId]);
                } else {
                    // Crear nueva PiezasProducto
                    $piezasProducto = new PiezasProducto();
                    $piezasProducto->setProducto($producto); // Establece la relación ManyToOne
                    $em->persist($piezasProducto);
                }
                
                // Actualizar datos de PiezasProducto
                if (isset($piezaData['cantidad'])) {
                    $piezasProducto->setCantidad((int) $piezaData['cantidad']);
                }
                
                // Actualizar relación con Piezas
                if (isset($piezaData['pieza'])) {
                    $pieza = $em->getRepository(Piezas::class)
                        ->find($piezaData['pieza']);
                    if ($pieza) {
                        $piezasProducto->setPieza($pieza);
                    } else {
                        $piezasProducto->setPieza(null);
                    }
                }
                
                // Actualizar relación con Activo
                if (isset($piezaData['activo'])) {
                    $activo = $em->getRepository(Activo::class)
                        ->find($piezaData['activo']);
                    if ($activo) {
                        $piezasProducto->setActivo($activo);
                    } else {
                        $piezasProducto->setActivo(null);
                    }
                }
                
                // Validar PiezasProducto
                $piezaErrors = $validator->validate($piezasProducto);
                if ($piezaErrors->count() > 0) {
                    $errorMessages = [];
                    foreach ($piezaErrors as $error) {
                        $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                    }
                    throw new \RuntimeException('Pieza inválida: ' . json_encode($errorMessages));
                }
                
                $piezasToKeep[] = $piezasProducto->getId();
                
            } catch (\Exception $e) {
                throw new \RuntimeException('Error procesando pieza: ' . $e->getMessage());
            }
        }
        
        // Eliminar PiezasProducto que ya no están en la lista
        foreach ($existingPiezasMap as $piezaProductoToDelete) {
            $em->remove($piezaProductoToDelete);
        }
    }
    
    public function getProductoById(int $id): array
    {
        try {
            // Buscar el producto por ID
            $producto = $this->find($id);
            
            if (!$producto) {
                return [
                    'error' => 'Producto no encontrado',
                    'success' => false
                ];
            }

            $piezas = [];
            
            // Verificar si hay piezas y recorrerlas
            if ($producto->getPiezasProducto() && !$producto->getPiezasProducto()->isEmpty()) {
                foreach ($producto->getPiezasProducto() as $piezasProducto) {
                    $piezaData = [
                        'id' => $piezasProducto->getId(),
                        'nombre' => $piezasProducto->getNombre(),
                        'cantidad' => $piezasProducto->getCantidad(),
                        'gramos' => $piezasProducto->getGramos(),
                        'metros' => $piezasProducto->getMetros(),
                        'horas' => $piezasProducto->getHoras(),
                        'minutos' => $piezasProducto->getMinutos(),
                        'precioMaterial' => $piezasProducto->getPrecioMaterial(),
                        'tipo' => $piezasProducto->getTipo(),
                    ];
                    
                    // Obtener datos del activo relacionado si existe
                    if ($piezasProducto->getActivo()) {
                        $activo = $piezasProducto->getActivo();
                        $piezaData['activo'] = [
                            'id' => $activo->getId(),
                            'nombre' => $activo->getNombre() ?? null,
                        ];
                    }
                    
                    // Obtener datos de la máquina relacionada si existe
                    if ($piezasProducto->getMaquina()) {
                        $maquina = $piezasProducto->getMaquina();
                        $piezaData['maquina'] = [
                            'id' => $maquina->getId(),
                            'nombre' => $maquina->getNombre() ?? null,
                        ];
                    }
                    
                    $piezas[] = $piezaData;
                }
            }
            
            // Construir respuesta
            $result = [
                'success' => true,
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'medida' => $producto->getMedida(),
                'clasificacion' => $producto->getClasificacion(),
                'descripcion' => $producto->getDescripcion(),
                'sku' => $producto->getSku(),
                'perfil' => $producto->getPerfil() ? [
                    'id' => $producto->getPerfil()->getId(),
                    'nombre' => $producto->getPerfil()->getNombre()
                ] : null,
                'tasaFallo' => $producto->getTasaFallo(),
                'tiempoSetup' => $producto->getTiempoSetup(),
                'postProcesado' => $producto->getPostProcesado(),
                'margenGanancia' => $producto->getMargenGanancia(),
                'empresa' => $producto->getEmpresa() ? $producto->getEmpresa()->getId() : null,
                'createAt' => $producto->getCreateAt() ? $producto->getCreateAt()->format('Y-m-d H:i:s') : null,
                'createBy' => $producto->getCreateBy(),
                'updateAt' => $producto->getUpdateAt() ? $producto->getUpdateAt()->format('Y-m-d H:i:s') : null,
                'updateBy' => $producto->getUpdateBy(),
                'piezas' => $piezas
            ];

            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener el producto: ' . $e->getMessage()
            ];
        }
    }
}