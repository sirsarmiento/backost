<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Codigo;
use App\Entity\Costo\Producto;
use App\Entity\Costo\Familia;
use App\Entity\Costo\SubFamilia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

/**
 * @method Codigo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Codigo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Codigo[]    findAll()
 * @method Codigo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodigoRepository extends ServiceEntityRepository
{

    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Codigo::class);
    }

    /**
     * Create Codigo.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Crear entidad principal
            $entity = $helper->setParametersToEntity(new Codigo(), $data);

            // Variable para almacenar el producto encontrado
            $productoEntity = null;

            // Manejar relaciones
            if (isset($data['producto']) && is_numeric($data['producto'])) {
                $productoEntity = $entityManager->getRepository(Producto::class)
                    ->find($data['producto']);
                if ($productoEntity) {
                    $entity->setProducto($productoEntity);
                    
                    // Actualizar el SKU del producto con el valor del código
                    if (isset($data['codigo']) && !empty($data['codigo'])) {
                        $productoEntity->setSku($data['codigo']);
                    }
                } else {
                    return new JsonResponse([
                        'msg' => 'Producto no encontrado',
                        'errors' => ['producto' => 'El producto especificado no existe']
                    ], 404);
                }
            }

            if (isset($data['familia']) && is_numeric($data['familia'])) {
                $familia = $entityManager->getRepository(Familia::class)
                    ->find($data['familia']);
                if ($familia) {
                    $entity->setFamilia($familia);
                }
            }

            if (isset($data['subfamilia']) && is_numeric($data['subfamilia'])) {
                $subfamilia = $entityManager->getRepository(SubFamilia::class)
                    ->find($data['subfamilia']);
                if ($subfamilia) {
                    $entity->setSubfamilia($subfamilia);
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
                    'msg' => 'Errores de validación',
                    'errors' => $errorMessages
                ], 422);
            }
            
            // Obtener usuario actual
            $currentUser = $entityManager->getRepository(User::class)
                ->find($this->security->getUser()->getId());
            
            if (!$currentUser) {
                return new JsonResponse(['msg' => 'Usuario no encontrado'], 404);
            }
            
            $entity->setCreateBy($currentUser->getUserName());

            // Persistir y flush
            $entityManager->persist($entity);
            
            // Si se actualizó el producto, también se persiste automáticamente
            // porque ya está siendo gestionado por el EntityManager
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Código creado exitosamente y SKU del producto actualizado',
                'id' => $entity->getId(),
                'producto_sku_actualizado' => $productoEntity ? $productoEntity->getSku() : null
            ], 201);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all Codigos.
     */
    public function getAll(): array 
    {
        try {
            $codigos = $this->findAll();
            $result = [];

            foreach ($codigos as $codigo) {
                $item = [
                    'id' => $codigo->getId(),
                    'categoria' => $codigo->getCategoria(),
                    'tecnologia' => $codigo->getTecnologia(),
                    'material' => $codigo->getMaterial(),
                    'codigo' => $codigo->getCodigo(),
                    'catalogo' => $codigo->getCatalogo(),
                    'createAt' => $codigo->getCreateAt()->format('Y-m-d H:i:s'),
                    'createBy' => $codigo->getCreateBy(),
                ];

                // Incluir relaciones si existen
                if ($codigo->getProducto()) {
                    $item['producto'] = [
                        'id' => $codigo->getProducto()->getId(),
                        'nombre' => $codigo->getProducto()->getNombre() // Ajusta según tu entidad Producto
                    ];
                }

                if ($codigo->getFamilia()) {
                    $item['familia'] = [
                        'id' => $codigo->getFamilia()->getId(),
                        'nombre' => $codigo->getFamilia()->getNombre() // Ajusta según tu entidad Familia
                    ];
                }

                if ($codigo->getSubfamilia()) {
                    $item['subfamilia'] = [
                        'id' => $codigo->getSubfamilia()->getId(),
                        'nombre' => $codigo->getSubfamilia()->getNombre() // Ajusta según tu entidad SubFamilia
                    ];
                }

                $result[] = $item;
            }

            return $result;
            
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Codigo by ID.
     */
    public function getById(int $id): ?array
    {
        try {
            $codigo = $this->find($id);
            
            if (!$codigo) {
                return null;
            }

            $result = [
                'id' => $codigo->getId(),
                'categoria' => $codigo->getCategoria(),
                'tecnologia' => $codigo->getTecnologia(),
                'material' => $codigo->getMaterial(),
                'codigo' => $codigo->getCodigo(),
                'catalogo' => $codigo->getCatalogo(),
                'createAt' => $codigo->getCreateAt()->format('Y-m-d H:i:s'),
                'createBy' => $codigo->getCreateBy(),
            ];

            if ($codigo->getUpdateAt()) {
                $result['updateAt'] = $codigo->getUpdateAt()->format('Y-m-d H:i:s');
                $result['updateBy'] = $codigo->getUpdateBy();
            }

            // Incluir relaciones si existen
            if ($codigo->getProducto()) {
                $result['producto'] = [
                    'id' => $codigo->getProducto()->getId(),
                    'nombre' => $codigo->getProducto()->getNombre()
                ];
            }

            if ($codigo->getFamilia()) {
                $result['familia'] = [
                    'id' => $codigo->getFamilia()->getId(),
                    'nombre' => $codigo->getFamilia()->getNombre()
                ];
            }

            if ($codigo->getSubfamilia()) {
                $result['subfamilia'] = [
                    'id' => $codigo->getSubfamilia()->getId(),
                    'nombre' => $codigo->getSubfamilia()->getNombre()
                ];
            }

            return $result;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update Codigo.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar el Código existente
            $codigo = $this->find($id);
            
            if (!$codigo) {
                return new JsonResponse(['msg' => 'Código no encontrado'], 404);
            }

            // Guardar el producto original para referencia
            $productoOriginal = $codigo->getProducto();
            $codigoOriginal = $codigo->getCodigo();

            // Actualizar entidad principal
            $codigo = $helper->setParametersToEntity($codigo, $data);

            // Variable para almacenar el producto actualizado
            $productoActualizado = null;

            // Manejar relaciones
            if (isset($data['producto'])) {
                if ($data['producto'] === null || $data['producto'] === '') {
                    // Si se elimina el producto, también limpiamos el SKU del producto anterior
                    if ($productoOriginal && $productoOriginal->getSku() === $codigoOriginal) {
                        $productoOriginal->setSku(null);
                    }
                    $codigo->setProducto(null);
                } elseif (is_numeric($data['producto'])) {
                    $productoEntity = $entityManager->getRepository(Producto::class)
                        ->find($data['producto']);
                    if ($productoEntity) {
                        $codigo->setProducto($productoEntity);
                        $productoActualizado = $productoEntity;
                        
                        // Actualizar el SKU del nuevo producto con el código actual
                        if (isset($data['codigo']) && !empty($data['codigo'])) {
                            $productoEntity->setSku($data['codigo']);
                        } elseif ($codigo->getCodigo()) {
                            $productoEntity->setSku($codigo->getCodigo());
                        }
                        
                        // Limpiar el SKU del producto anterior si era el mismo código
                        if ($productoOriginal && $productoOriginal !== $productoEntity && 
                            $productoOriginal->getSku() === $codigoOriginal) {
                            $productoOriginal->setSku(null);
                        }
                    } else {
                        return new JsonResponse([
                            'msg' => 'Producto no encontrado',
                            'errors' => ['producto' => 'El producto especificado no existe']
                        ], 404);
                    }
                }
            } else {
                // Si no se cambió el producto pero se cambió el código, actualizar el SKU del producto actual
                if ($productoOriginal && isset($data['codigo']) && $data['codigo'] !== $codigoOriginal) {
                    $productoOriginal->setSku($data['codigo']);
                    $productoActualizado = $productoOriginal;
                }
            }

            if (isset($data['familia'])) {
                if ($data['familia'] === null || $data['familia'] === '') {
                    $codigo->setFamilia(null);
                } elseif (is_numeric($data['familia'])) {
                    $familia = $entityManager->getRepository(Familia::class)
                        ->find($data['familia']);
                    $codigo->setFamilia($familia);
                }
            }

            if (isset($data['subfamilia'])) {
                if ($data['subfamilia'] === null || $data['subfamilia'] === '') {
                    $codigo->setSubfamilia(null);
                } elseif (is_numeric($data['subfamilia'])) {
                    $subfamilia = $entityManager->getRepository(SubFamilia::class)
                        ->find($data['subfamilia']);
                    $codigo->setSubfamilia($subfamilia);
                }
            }
            
            // Validar entidad principal
            $errors = $validator->validate($codigo);
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
                $codigo->setUpdateBy($currentUser->getUserName());
                $codigo->setUpdateAt(new \DateTime());
            }

            // Persistir y flush
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Registro actualizado exitosamente',
                'id' => $codigo->getId(),
                'producto_sku_actualizado' => $productoActualizado ? $productoActualizado->getSku() : 
                                            ($productoOriginal ? $productoOriginal->getSku() : null)
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete Codigo.
     */
    public function delete(int $id): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            $codigo = $this->find($id);
            
            if (!$codigo) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Código no encontrado'
                ], 404);
            }

            $entityManager->remove($codigo);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Código eliminado exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error al eliminar el código',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
