<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Costo;
use App\Repository\Costo\ProductoRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;
Use App\Entity\User;

/**
 * @method Costo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Costo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Costo[]    findAll()
 * @method Costo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CostoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Costo::class);
    }

    /**
     * Create costo.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Crear entidad principal - costo
            $entity = $helper->setParametersToEntity(new Costo(), $data);
            
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
                'msg' => 'costo creado exitosamente',
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
        $costos = $this->findAll();

        $result = [];

        foreach ($costos as $costo) {      
            $result[] = [
                'id' => $costo->getId(),
                'tipo' => $costo->getTipo(),
                'concepto' => $costo->getConcepto(),
                'clasificacion' => $costo->getClasificacion(),
                'precio' => $costo->getPrecio(),
                'producto' => $costo->getProducto() ? $costo->getProducto()->getId() : null,
                'productoName' => $costo->getProducto() ? $costo->getProducto()->getNombre() : null,
            ];
        }

        return $result;
        
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error al obtener los costos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update costo.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar el costo existente
            $costo = $this->find($id);
            
            if (!$costo) {
                return new JsonResponse(['msg' => 'costo no encontrado'], 404);
            }

            // Actualizar entidad principal
            $costo = $helper->setParametersToEntity($costo, $data);
            
            // Validar entidad principal
            $errors = $validator->validate($costo);
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
                $costo->setUpdateBy($currentUser->getUserName());
                $costo->setUpdateAt(new \DateTime());
            }

            // Persistir y flush
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Registro actualizado exitosamente',
                'id' => $costo->getId()
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
