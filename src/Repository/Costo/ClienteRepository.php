<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Cliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

/**
 * @method Cliente|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cliente|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cliente[]    findAll()
 * @method Cliente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClienteRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Cliente::class);
    }

    /**
     * Create Cliente.
     */
    public function post($data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Crear entidad principal - Cliente
            $entity = $helper->setParametersToEntity(new Cliente(), $data);
            
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
            
            // Persistir y flush
            $entityManager->persist($entity);
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Cliente creado exitosamente',
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
            $clientes = $this->findAll();
            $result = [];

            foreach ($clientes as $cliente) {      
                $result[] = [
                    'id' => $cliente->getId(),
                    'nombre' => $cliente->getNombre(),
                    'apellido' => $cliente->getApellido(),
                    'email' => $cliente->getEmail(),
                    'telefono' => $cliente->getTelefono(),
                    'direccion' => $cliente->getDireccion(),
                    'cedula' => $cliente->getCedula(),
                    'categoria' => $cliente->getCategoria(),
                ];
            }

            return $result;
            
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Update Cliente.
     */
    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entityManager = $this->getEntityManager();

        try {
            // Buscar el Cliente existente
            $cliente = $this->find($id);
            
            if (!$cliente) {
                return new JsonResponse(['msg' => 'Cliente no encontrado'], 404);
            }

            // Actualizar entidad principal
            $cliente = $helper->setParametersToEntity($cliente, $data);
            
            // Validar entidad principal
            $errors = $validator->validate($cliente);
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

            // Persistir y flush
            $entityManager->flush();
            
            return new JsonResponse([
                'msg' => 'Registro actualizado exitosamente',
                'id' => $cliente->getId()
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'msg' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}