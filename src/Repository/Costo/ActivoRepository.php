<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Activo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;
Use App\Entity\User;

/**
 * @method Activo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activo[]    findAll()
 * @method Activo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Activo::class);
    }

    /**
     * Create Activo.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Crear entidad principal - Activo
            $entity = $helper->setParametersToEntity(new Activo(), $data);
            
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

            // Persistir y flush
            $entityManager->persist($entity);
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Activo creado exitosamente',
                'id' => $entity->getId()
            ], 201);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAll(): array 
    {
        try {
        $products = $this->findAll();

        $result = [];

        foreach ($products as $product) {      
            $result[] = [
                'id' => $product->getId(),
                'nombre' => $product->getNombre(),
                'costoInicial' => $product->getCostoInicial(),
                'valorResidual' => $product->getValorResidual(),
                'vidaUtil' => $product->getVidaUtil(),
                'fechaCompra' => $product->getFechaCompra()->format('Y-m-d'),
            ];
        }

        return $result;
        
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error al obtener los productes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Activo.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar el Activo existente
            $activo = $this->find($id);
            
            if (!$activo) {
                return new JsonResponse(['msg' => 'Activo no encontrado'], 404);
            }

            // Actualizar entidad principal
            $activo = $helper->setParametersToEntity($activo, $data);
            
            // Validar entidad principal
            $errors = $validator->validate($activo);
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
                $activo->setUpdateBy($currentUser->getUserName());
                $activo->setUpdateAt(new \DateTime());
            }

            // Persistir y flush
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Registro actualizado exitosamente',
                'id' => $activo->getId()
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
