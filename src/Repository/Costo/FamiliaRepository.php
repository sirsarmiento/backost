<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Familia;
use App\Entity\Costo\SubFamilia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;
use App\Entity\User;

/**
 * @method Familia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Familia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Familia[]    findAll()
 * @method Familia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FamiliaRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Familia::class);
    }

    /**
     * Create Familia Empresa.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();
    
        try {
            // Validar datos requeridos
            if (!isset($data['codigo']) || !isset($data['nombre'])) {
                return new JsonResponse([
                    'msg' => 'Datos incompletos: codigo y nombre son requeridos'
                ], 400);
            }
            
            // Crear entidad principal
            $entity = $helper->setParametersToEntity(new Familia(), $data);
            
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
            
            // Asignar empresa si se proporciona el ID
            if (isset($data['empresaId'])) {
                $empresa = $entityManager->getRepository(Empresa::class)
                    ->find($data['empresaId']);
                
                if ($empresa) {
                    $entity->setEmpresa($empresa);
                }
            }

            // Procesar subfamilias si existen
            if (isset($data['subFamilias']) && is_array($data['subFamilias'])) {
                $this->processSubFamilias($entity, $data['subFamilias'], $entityManager, $validator);
            }
            
            // Persistir y flush
            $entityManager->persist($entity);
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Registro creado exitosamente',
                'id' => $entity->getId()
            ], 201);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process subfamilias for a familia
     */
    private function processSubFamilias(Familia $familia, array $subFamilias, EntityManagerInterface $em, $validator): void
    {
        foreach ($subFamilias as $subFamiliaData) {
            try {
                // Validar datos requeridos de subfamilia
                if (!isset($subFamiliaData['codigo']) || !isset($subFamiliaData['nombre'])) {
                    throw new \RuntimeException('Subfamilia inválida: código y nombre son requeridos');
                }
                
                // Crear entidad de subfamilia
                $subFamilia = new SubFamilia();
                
                // Asignar datos a la subfamilia
                $subFamilia->setCodigo($subFamiliaData['codigo']);
                $subFamilia->setNombre($subFamiliaData['nombre']);
                
                // Relacionar con la familia
                $subFamilia->setFamilia($familia);
                
                // Validar subfamilia individual
                $subFamiliaErrors = $validator->validate($subFamilia);
                if ($subFamiliaErrors->count() > 0) {
                    $errorMessages = [];
                    foreach ($subFamiliaErrors as $error) {
                        $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                    }
                    throw new \RuntimeException('Subfamilia inválida: ' . json_encode($errorMessages));
                }
                
                $em->persist($subFamilia);
                
            } catch (\Exception $e) {
                throw new \RuntimeException('Error procesando subfamilia: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get all familias with their subfamilias
     */
    public function getAllWithSubFamilias(): array 
    {
        try {
            $familias = $this->findAll();
            $result = [];

            foreach ($familias as $familia) {
                $subFamilias = [];
                
                // Verificar si hay subfamilias y recorrerlas
                if ($familia->getSubFamilias() && !$familia->getSubFamilias()->isEmpty()) {
                    foreach ($familia->getSubFamilias() as $subFamilia) {
                        $subFamilias[] = [
                            'id' => $subFamilia->getId(),
                            'codigo' => $subFamilia->getCodigo(),
                            'nombre' => $subFamilia->getNombre()
                        ];
                    }
                }
                
                $result[] = [
                    'id' => $familia->getId(),
                    'codigo' => $familia->getCodigo(),
                    'nombre' => $familia->getNombre(),
                    'empresaId' => $familia->getEmpresa() ? $familia->getEmpresa()->getId() : null,
                    'createAt' => $familia->getCreateAt() ? $familia->getCreateAt()->format('Y-m-d H:i:s') : null,
                    'createBy' => $familia->getCreateBy(),
                    'updateAt' => $familia->getUpdateAt() ? $familia->getUpdateAt()->format('Y-m-d H:i:s') : null,
                    'updateBy' => $familia->getUpdateBy(),
                    'subFamilias' => $subFamilias
                ];
            }

            return $result;
            
        } catch (\Exception $e) {
            throw new \RuntimeException('Error al obtener las familias: ' . $e->getMessage());
        }
    }

    /**
     * Update Familia Empresa.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar la familia existente
            $familia = $this->find($id);
            
            if (!$familia) {
                return new JsonResponse(['msg' => 'Familia no encontrada'], 404);
            }

            // Actualizar entidad principal
            $familia = $helper->setParametersToEntity($familia, $data);
            
            // Validar entidad principal
            $errors = $validator->validate($familia);
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
                $familia->setUpdateBy($currentUser->getUserName());
                $familia->setUpdateAt(new \DateTime());
            }

            // Actualizar empresa si se proporciona
            if (isset($data['empresaId'])) {
                $empresa = $entityManager->getRepository(Empresa::class)
                    ->find($data['empresaId']);
                
                if ($empresa) {
                    $familia->setEmpresa($empresa);
                }
            }

            // Procesar subfamilias si existen
            if (isset($data['subFamilias']) && is_array($data['subFamilias'])) {
                $this->updateSubFamilias($familia, $data['subFamilias'], $entityManager, $validator);
            }
            
            // Persistir y flush
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Registro actualizado exitosamente',
                'id' => $familia->getId()
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update subfamilias for a familia
     */
    private function updateSubFamilias(Familia $familia, array $subFamilias, EntityManagerInterface $em, $validator): void
    {
        // Obtener subfamilias existentes
        $existingSubFamilias = $familia->getSubFamilias();
        $existingSubFamiliasMap = [];
        
        foreach ($existingSubFamilias as $subFamilia) {
            $existingSubFamiliasMap[$subFamilia->getId()] = $subFamilia;
        }

        $subFamiliasToKeep = [];
        
        foreach ($subFamilias as $subFamiliaData) {
            try {
                // Validar datos requeridos
                if (!isset($subFamiliaData['codigo']) || !isset($subFamiliaData['nombre'])) {
                    throw new \RuntimeException('Subfamilia inválida: código y nombre son requeridos');
                }
                
                $subFamiliaId = $subFamiliaData['id'] ?? null;
                
                if ($subFamiliaId && isset($existingSubFamiliasMap[$subFamiliaId])) {
                    // Actualizar subfamilia existente
                    $subFamilia = $existingSubFamiliasMap[$subFamiliaId];
                    unset($existingSubFamiliasMap[$subFamiliaId]);
                } else {
                    // Crear nueva subfamilia
                    $subFamilia = new SubFamilia();
                    $subFamilia->setFamilia($familia);
                    $em->persist($subFamilia);
                }
                
                // Actualizar datos de la subfamilia
                $subFamilia->setCodigo($subFamiliaData['codigo']);
                $subFamilia->setNombre($subFamiliaData['nombre']);
                
                // Validar subfamilia
                $subFamiliaErrors = $validator->validate($subFamilia);
                if ($subFamiliaErrors->count() > 0) {
                    $errorMessages = [];
                    foreach ($subFamiliaErrors as $error) {
                        $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                    }
                    throw new \RuntimeException('Subfamilia inválida: ' . json_encode($errorMessages));
                }
                
                $subFamiliasToKeep[] = $subFamilia->getId();
                
            } catch (\Exception $e) {
                throw new \RuntimeException('Error procesando subfamilia: ' . $e->getMessage());
            }
        }
        
        // Eliminar subfamilias que ya no están en la lista
        foreach ($existingSubFamiliasMap as $subFamiliaToDelete) {
            $em->remove($subFamiliaToDelete);
        }
    }

    /**
     * Delete Familia Empresa.
     */
    public function delete(int $id): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar la familia existente
            $familia = $this->find($id);
            
            if (!$familia) {
                return new JsonResponse(['msg' => 'Familia no encontrada'], 404);
            }

            // Eliminar la familia (las subfamilias se eliminarán en cascada si está configurado)
            $entityManager->remove($familia);
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Familia eliminada exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}