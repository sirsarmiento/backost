<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Perfil;
use App\Entity\Costo\Parametro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;
Use App\Entity\User;

/**
 * @method Perfil|null find($id, $lockMode = null, $lockVersion = null)
 * @method Perfil|null findOneBy(array $criteria, array $orderBy = null)
 * @method Perfil[]    findAll()
 * @method Perfil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PerfilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
         $this->security = $security;
        parent::__construct($registry, Perfil::class);
    }

    /**
     * Create Perfil Empresa.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();
    
        try {
            // Crear entidad principal
            $entity = $helper->setParametersToEntity(new Perfil(), $data);
            
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
            
            // Asignar empresa
            $empresa = $entityManager->getRepository(Empresa::class)
                ->find($this->security->getUser()->getIdempresa());
            
            if ($empresa) {
                $entity->setEmpresa($empresa);
            }

            // Procesar parámetros si existen
            if (isset($data['parametros']) && is_array($data['parametros'])) {
                $this->processParameters($entity, $data['parametros'], $entityManager, $validator);
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

    // Cambiado EntityManagerInterface por EntityManager
    private function processParameters(Perfil $perfil, array $parametros, EntityManagerInterface $em, $validator): void
    {
        foreach ($parametros as $parametroData) {
            try {
                // Crear entidad de parámetro
                $parametro = new Parametro(); // Asegúrate de que esta clase existe
                
                // Asignar datos al parámetro - ajusta según los métodos reales de tu entidad
                $parametro->setTipo($parametroData['tipo'] ?? null);
                $parametro->setDescripcion($parametroData['descripcion'] ?? null);
                $parametro->setUnidad($parametroData['unidad'] ?? null);
                $parametro->setProdMaxHoras($parametroData['prodMaxHoras'] ?? null);
                $parametro->setHorasMax($parametroData['horasMax'] ?? null);
                $parametro->setHorasUso($parametroData['horasUso'] ?? null);
                
                // Relacionar con el perfil - asegúrate de que este método existe
                $parametro->setPerfil($perfil);
                
                // Validar parámetro individual
                $parametroErrors = $validator->validate($parametro);
                if ($parametroErrors->count() > 0) {
                    $errorMessages = [];
                    foreach ($parametroErrors as $error) {
                        $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                    }
                    throw new \RuntimeException('Parámetro inválido: ' . json_encode($errorMessages));
                }
                
                $em->persist($parametro);
                
            } catch (\Exception $e) {
                throw new \RuntimeException('Error procesando parámetro: ' . $e->getMessage());
            }
        }
    }

    public function getAllWithParametros(): array 
    {
        try {
            $perfiles = $this->findAll();

        $result = [];

        foreach ($perfiles as $perfil) {
            $parametros = [];
            
            // Verificar si hay parámetros y recorrerlos
            if ($perfil->getParametros() && !$perfil->getParametros()->isEmpty()) {
                foreach ($perfil->getParametros() as $parametro) {
                    $parametros[] = [
                        'id' => $parametro->getId(),
                        'tipo' => $parametro->getTipo(),
                        'descripcion' => $parametro->getDescripcion(),
                        'unidad' => $parametro->getUnidad(),
                        'prodMaxHoras' => $parametro->getProdMaxHoras(),
                        'horasMax' => $parametro->getHorasMax(),
                        'horasUso' => $parametro->getHorasUso()
                    ];
                }
            }
            
            $result[] = [
                'id' => $perfil->getId(),
                'nombre' => $perfil->getNombre(),
                'tipo' => $perfil->getTipo(),
                'sector' => $perfil->getSector(),
                'empleados' => $perfil->getEmpleados(),
                'rif' => $perfil->getRif(),
                'periodo' => $perfil->getPeriodo(),
                'direccion' => $perfil->getDireccion(),
                'moneda' => $perfil->getMoneda(),
                'parametros' => $parametros
            ];
        }

        return $result;
        
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error al obtener los perfiles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Perfil Empresa.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar el perfil existente
            $perfil = $this->find($id);
            
            if (!$perfil) {
                return new JsonResponse(['msg' => 'Perfil no encontrado'], 404);
            }

            // Actualizar entidad principal
            $perfil = $helper->setParametersToEntity($perfil, $data);
            
            // Validar entidad principal
            $errors = $validator->validate($perfil);
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
                $perfil->setUpdateBy($currentUser->getUserName());
                $perfil->setUpdateAt(new \DateTime());
            }

            // Procesar parámetros si existen
            if (isset($data['parametros']) && is_array($data['parametros'])) {
                $this->updateParameters($perfil, $data['parametros'], $entityManager, $validator);
            }
            
            // Persistir y flush
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Registro actualizado exitosamente',
                'id' => $perfil->getId()
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function updateParameters(Perfil $perfil, array $parametros, EntityManagerInterface $em, $validator): void
    {
        // Obtener parámetros existentes
        $existingParametros = $perfil->getParametros();
        $existingParametrosMap = [];
        
        foreach ($existingParametros as $parametro) {
            $existingParametrosMap[$parametro->getId()] = $parametro;
        }

        $parametrosToKeep = [];
        
        foreach ($parametros as $parametroData) {
            try {
                $parametroId = $parametroData['id'] ?? null;
                
                if ($parametroId && isset($existingParametrosMap[$parametroId])) {
                    // Actualizar parámetro existente
                    $parametro = $existingParametrosMap[$parametroId];
                    unset($existingParametrosMap[$parametroId]);
                } else {
                    // Crear nuevo parámetro
                    $parametro = new Parametro();
                    $parametro->setPerfil($perfil);
                    $em->persist($parametro);
                }
                
                // Actualizar datos del parámetro
                $parametro->setTipo($parametroData['tipo'] ?? null);
                $parametro->setDescripcion($parametroData['descripcion'] ?? null);
                $parametro->setUnidad($parametroData['unidad'] ?? null);
                $parametro->setProdMaxHoras($parametroData['prodMaxHoras'] ?? null);
                $parametro->setHorasMax($parametroData['horasMax'] ?? null);
                $parametro->setHorasUso($parametroData['horasUso'] ?? null);
                
                // Validar parámetro
                $parametroErrors = $validator->validate($parametro);
                if ($parametroErrors->count() > 0) {
                    $errorMessages = [];
                    foreach ($parametroErrors as $error) {
                        $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                    }
                    throw new \RuntimeException('Parámetro inválido: ' . json_encode($errorMessages));
                }
                
                $parametrosToKeep[] = $parametro->getId();
                
            } catch (\Exception $e) {
                throw new \RuntimeException('Error procesando parámetro: ' . $e->getMessage());
            }
        }
        
        // Eliminar parámetros que ya no están en la lista
        foreach ($existingParametrosMap as $parametroToDelete) {
            $em->remove($parametroToDelete);
        }
    }
}
