<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Presupuesto;
use App\Entity\Costo\Piezas;
use App\Entity\Costo\Producto;
use App\Entity\Costo\Activo;
use App\Entity\Costo\Cliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;
Use App\Entity\User;

/**
 * @method Presupuesto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Presupuesto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Presupuesto[]    findAll()
 * @method Presupuesto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PresupuestoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Presupuesto::class);
    }

    /**
     * Create Presupuesto con Piezas.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Validar datos requeridos
            $requiredFields = ['clasificacion', 'descripcion', 'numero', 'fecha', 'piezas'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Datos incompletos',
                        'error' => "El campo '$field' es requerido"
                    ], 400);
                }
            }

            // Validar que piezas sea un array no vacío
            if (!is_array($data['piezas']) || empty($data['piezas'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'error' => 'El campo "piezas" debe ser un array no vacío'
                ], 400);
            }

            // Crear entidad principal
            $entity = new Presupuesto();
            
            // Asignar propiedades básicas
            $entity->setClasificacion($data['clasificacion']);
            $entity->setDescripcion($data['descripcion']);
            $entity->setNumero($data['numero']);
            
            // Convertir y asignar fecha
            try {
                $fecha = new \DateTime($data['fecha']);
                $entity->setFecha($fecha);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Formato de fecha inválido',
                    'error' => 'La fecha debe estar en formato ISO 8601'
                ], 400);
            }

            // Asignar nuevas propiedades si existen
            if (isset($data['costoOperador'])) {
                $entity->setCostoOperador((float) $data['costoOperador']);
            }
            
            if (isset($data['costoMaquina'])) {
                $entity->setCostoMaquina((float) $data['costoMaquina']);
            }
            
            if (isset($data['tasaFalloGlobal'])) {
                $entity->setTasaFalloGlobal((float) $data['tasaFalloGlobal']);
            }
            
            if (isset($data['tiempoSetup'])) {
                $entity->setTiempoSetup((int) $data['tiempoSetup']);
            }
            
            if (isset($data['margenGanancia'])) {
                $entity->setMargenGanancia((float) $data['margenGanancia']);
            }
            
            if (isset($data['tiempoPostProcesado'])) {
                $entity->setTiempoPostProcesado((int) $data['tiempoPostProcesado']);
            }

            if (isset($data['cantidadGlobal'])) {
                $entity->setCantidadGlobal((int) $data['cantidadGlobal']);
            }

            if (isset($data['delivery'])) {
                $entity->setDelivery((float) $data['delivery']);
            }

            if (isset($data['cliente'])) {
                $cliente = $entityManager->getRepository(Cliente::class)->find($data['cliente']);
                if ($cliente) {
                    $entity->setCliente($cliente);
                }
            }

            if (isset($data['producto'])) {
                $producto = $entityManager->getRepository(Producto::class)->find($data['producto']);
                if ($producto) {
                    $entity->setProducto($producto);
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
            
            // Asignar empresa si es necesario
            if (method_exists($entity, 'setEmpresa')) {
                $empresa = $entityManager->getRepository(Empresa::class)
                    ->find($this->security->getUser()->getIdempresa());
                
                if ($empresa) {
                    $entity->setEmpresa($empresa);
                }
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

            // Procesar piezas
            $this->processPiezas($entity, $data['piezas'], $entityManager, $validator);

            // Persistir y flush
            $entityManager->persist($entity);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Presupuesto y piezas creados exitosamente',
                'presupuestoId' => $entity->getId()
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
     * Procesa las piezas asociadas al presupuesto
     */
    private function processPiezas(Presupuesto $presupuesto, array $piezasData, $entityManager, $validator): void
    {
        foreach ($piezasData as $piezaData) {
            // Validar campos requeridos para pieza
            $requiredPiezaFields = ['nombre', 'gramos', 'metros', 'horas', 'minutos', 'precioMaterial'];
            foreach ($requiredPiezaFields as $field) {
                if (!isset($piezaData[$field])) {
                    throw new \InvalidArgumentException("El campo '$field' es requerido para cada pieza");
                }
            }

            // Crear nueva pieza
            $pieza = new Piezas();
            $pieza->setNombre($piezaData['nombre']);
            $pieza->setGramos((float) $piezaData['gramos']);
            $pieza->setMetros((float) $piezaData['metros']);
            $pieza->setHoras((int) $piezaData['horas']);
            $pieza->setMinutos((int) $piezaData['minutos']);
            $pieza->setPrecioMaterial((float) $piezaData['precioMaterial']);
            $pieza->setPresupuesto($presupuesto);
            
            // Asignar nuevas propiedades de pieza
            if (isset($piezaData['tipo'])) {
                $pieza->setTipo($piezaData['tipo']);
            }
            
            if (isset($piezaData['cantidad'])) {
                $pieza->setCantidad((int) $piezaData['cantidad']);
            }
            
            // Asignar relaciones si existen
            if (isset($piezaData['producto'])) {
                $producto = $entityManager->getRepository(Producto::class)
                    ->find($piezaData['producto']);
                if ($producto) {
                    $pieza->setProducto($producto);
                }
            }
            
            if (isset($piezaData['activo'])) {
                $activo = $entityManager->getRepository(Activo::class)
                    ->find($piezaData['activo']);
                if ($activo) {
                    $pieza->setActivo($activo);
                }
            }
            
            if (isset($piezaData['maquina'])) {
                $maquina = $entityManager->getRepository(Activo::class)
                    ->find($piezaData['maquina']);
                if ($maquina) {
                    $pieza->setMaquina($maquina);
                }
            }
            
            // Validar pieza
            $errors = $validator->validate($pieza);
            if ($errors->count() > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                throw new \InvalidArgumentException('Error de validación en pieza: ' . json_encode($errorMessages));
            }
            
            $entityManager->persist($pieza);
            $presupuesto->addPieza($pieza);
        }
    }

    public function getAllWithParts(): array 
    {
        try {
            $presupuestos = $this->findAll();

            $result = [];

            foreach ($presupuestos as $presupuesto) {
                $piezas = [];
                
                // Verificar si hay piezas y recorrerlas
                if ($presupuesto->getPiezas() && !$presupuesto->getPiezas()->isEmpty()) {
                    foreach ($presupuesto->getPiezas() as $pieza) {
                        $piezaData = [
                            'id' => $pieza->getId(),
                            'nombre' => $pieza->getNombre(),
                            'gramos' => $pieza->getGramos(),
                            'metros' => $pieza->getMetros(),
                            'horas' => $pieza->getHoras(),
                            'minutos' => $pieza->getMinutos(),
                            'precioMaterial' => $pieza->getPrecioMaterial(),
                            'tipo' => $pieza->getTipo(),
                            'cantidad' => $pieza->getCantidad(),
                        ];
                        
                        // Obtener IDs de relaciones
                        if ($pieza->getProducto()) {
                            $piezaData['producto'] = $pieza->getProducto()->getId();
                        }
                        
                        if ($pieza->getActivo()) {
                            $piezaData['activo'] = $pieza->getActivo()->getId();
                        }
                        
                        if ($pieza->getMaquina()) {
                            $piezaData['maquina'] = $pieza->getMaquina()->getId();
                        }
                        
                        $piezas[] = $piezaData;
                    }
                }
                
                $result[] = [
                    'id' => $presupuesto->getId(),
                    'clasificacion' => $presupuesto->getClasificacion(),
                    'descripcion' => $presupuesto->getDescripcion(),
                    'numero' => $presupuesto->getNumero(),
                    'fecha' => $presupuesto->getFecha()->format('Y-m-d'),
                    'costoOperador' => $presupuesto->getCostoOperador(),
                    'costoMaquina' => $presupuesto->getCostoMaquina(),
                    'tasaFalloGlobal' => $presupuesto->getTasaFalloGlobal(),
                    'tiempoSetup' => $presupuesto->getTiempoSetup(),
                    'margenGanancia' => $presupuesto->getMargenGanancia(),
                    'tiempoPostProcesado' => $presupuesto->getTiempoPostProcesado(),
                    'cantidadGlobal' => $presupuesto->getCantidadGlobal(),
                    'delivery' => $presupuesto->getDelivery(),
                    'cliente' => $presupuesto->getCliente() ? $presupuesto->getCliente()->getId() : null,
                    'producto' => $presupuesto->getProducto() ? $presupuesto->getProducto()->getId() : null,
                    'piezas' => $piezas
                ];
            }

            return $result;
            
        } catch (\Exception $e) {
            // En lugar de devolver JsonResponse, lanzar excepción o devolver array con error
            return [
                'error' => 'Error al obtener los presupuestos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update Presupuesto.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar el presupuesto existente
            $presupuesto = $this->find($id);
            
            if (!$presupuesto) {
                return new JsonResponse(['msg' => 'Presupuesto no encontrado'], 404);
            }

            // Actualizar propiedades básicas
            if (isset($data['clasificacion'])) {
                $presupuesto->setClasificacion($data['clasificacion']);
            }
            if (isset($data['descripcion'])) {
                $presupuesto->setDescripcion($data['descripcion']);
            }
            if (isset($data['numero'])) {
                $presupuesto->setNumero($data['numero']);
            }
            if (isset($data['fecha'])) {
                try {
                    $fecha = new \DateTime($data['fecha']);
                    $presupuesto->setFecha($fecha);
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'msg' => 'Formato de fecha inválido'
                    ], 400);
                }
            }

            // Actualizar nuevas propiedades
            if (isset($data['costoOperador'])) {
                $presupuesto->setCostoOperador((float) $data['costoOperador']);
            }
            
            if (isset($data['costoMaquina'])) {
                $presupuesto->setCostoMaquina((float) $data['costoMaquina']);
            }
            
            if (isset($data['tasaFalloGlobal'])) {
                $presupuesto->setTasaFalloGlobal((float) $data['tasaFalloGlobal']);
            }
            
            if (isset($data['tiempoSetup'])) {
                $presupuesto->setTiempoSetup((int) $data['tiempoSetup']);
            }
            
            if (isset($data['margenGanancia'])) {
                $presupuesto->setMargenGanancia((float) $data['margenGanancia']);
            }
            
            if (isset($data['tiempoPostProcesado'])) {
                $presupuesto->setTiempoPostProcesado((int) $data['tiempoPostProcesado']);
            }

            if (isset($data['cantidadGlobal'])) {
                $presupuesto->setCantidadGlobal((int) $data['cantidadGlobal']);
            }

            if (isset($data['delivery'])) {
                $presupuesto->setDelivery((float) $data['delivery']);
            }

            if (isset($data['cliente'])) {
                $cliente = $entityManager->getRepository(Cliente::class)->find($data['cliente']);
                if ($cliente) {
                    $presupuesto->setCliente($cliente);
                } else {
                    $presupuesto->setCliente(null);
                }
            }

            if (isset($data['producto'])) {
                $producto = $entityManager->getRepository(Producto::class)->find($data['producto']);
                if ($producto) {
                    $presupuesto->setProducto($producto);
                } else {
                    $presupuesto->setProducto(null);
                }
            }
            
            // Validar entidad principal
            $errors = $validator->validate($presupuesto);
            if ($errors->count() > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                
                return new JsonResponse([
                    'msg' => 'Errores de validación',
                    'errors' => $errorMessages
                ], 422);
            }

            // Obtener usuario actual para auditoría
            $currentUser = $entityManager->getRepository(User::class)
                ->find($this->security->getUser()->getId());

            if ($currentUser) {
                $presupuesto->setUpdateBy($currentUser->getUserName());
                $presupuesto->setUpdateAt(new \DateTime());
            }

            // Procesar piezas si existen
            if (isset($data['piezas']) && is_array($data['piezas'])) {
                $this->updatePiezas($presupuesto, $data['piezas'], $entityManager, $validator);
            }
            
            // Persistir y flush
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Presupuesto actualizado exitosamente',
                'id' => $presupuesto->getId()
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function updatePiezas(Presupuesto $presupuesto, array $piezas, EntityManagerInterface $em, $validator): void
    {
        // Obtener piezas existentes
        $existingPiezas = $presupuesto->getPiezas();
        $existingPiezasMap = [];
        
        foreach ($existingPiezas as $pieza) {
            $existingPiezasMap[$pieza->getId()] = $pieza;
        }

        $piezasToKeep = [];
        
        foreach ($piezas as $piezaData) {
            try {
                $piezaId = $piezaData['id'] ?? null;
                
                if ($piezaId && isset($existingPiezasMap[$piezaId])) {
                    // Actualizar pieza existente
                    $pieza = $existingPiezasMap[$piezaId];
                    unset($existingPiezasMap[$piezaId]);
                } else {
                    // Crear nueva pieza
                    $pieza = new Piezas();
                    $pieza->setPresupuesto($presupuesto);
                    $em->persist($pieza);
                }
                
                // Actualizar datos de la pieza
                $pieza->setNombre($piezaData['nombre'] ?? null);
                $pieza->setGramos($piezaData['gramos'] ?? 0);
                $pieza->setMetros($piezaData['metros'] ?? 0);
                $pieza->setHoras($piezaData['horas'] ?? 0);
                $pieza->setMinutos($piezaData['minutos'] ?? 0);
                $pieza->setPrecioMaterial($piezaData['precioMaterial'] ?? 0);
                
                // Actualizar nuevas propiedades de pieza
                if (isset($piezaData['tipo'])) {
                    $pieza->setTipo($piezaData['tipo']);
                }
                
                if (isset($piezaData['cantidad'])) {
                    $pieza->setCantidad((int) $piezaData['cantidad']);
                }
                
                // Actualizar relaciones
                if (isset($piezaData['producto'])) {
                    $producto = $em->getRepository(Producto::class)
                        ->find($piezaData['producto']);
                    if ($producto) {
                        $pieza->setProducto($producto);
                    } else {
                        $pieza->setProducto(null);
                    }
                }
                
                if (isset($piezaData['activo'])) {
                    $activo = $em->getRepository(Activo::class)
                        ->find($piezaData['activo']);
                    if ($activo) {
                        $pieza->setActivo($activo);
                    } else {
                        $pieza->setActivo(null);
                    }
                }
                
                if (isset($piezaData['maquina'])) {
                    $maquina = $em->getRepository(Activo::class)
                        ->find($piezaData['maquina']);
                    if ($maquina) {
                        $pieza->setMaquina($maquina);
                    } else {
                        $pieza->setMaquina(null);
                    }
                }
                
                // Actualizar campos de auditoría
                $currentUser = $em->getRepository(User::class)
                    ->find($this->security->getUser()->getId());
                    
                if ($currentUser) {
                    $pieza->setUpdateBy($currentUser->getUserName());
                    $pieza->setUpdateAt(new \DateTime());
                }
                
                // Validar pieza
                $piezaErrors = $validator->validate($pieza);
                if ($piezaErrors->count() > 0) {
                    $errorMessages = [];
                    foreach ($piezaErrors as $error) {
                        $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                    }
                    throw new \RuntimeException('Pieza inválida: ' . json_encode($errorMessages));
                }
                
                $piezasToKeep[] = $pieza->getId();
                
            } catch (\Exception $e) {
                throw new \RuntimeException('Error procesando pieza: ' . $e->getMessage());
            }
        }
        
        // Eliminar piezas que ya no están en la lista
        foreach ($existingPiezasMap as $piezaToDelete) {
            $em->remove($piezaToDelete);
        }
    }
}