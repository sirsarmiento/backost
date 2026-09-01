<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Proveedor;
use App\Entity\Empresa;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class ProveedorRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Proveedor::class);
    }

    public function post($data, $validator, $helper): JsonResponse
    {
        $em = $this->getEntityManager();
        try {
            $entity = $helper->setParametersToEntity(new Proveedor(), $data);
            $user = $em->getRepository(User::class)->find($this->security->getUser()->getId());
            if ($user) {
                $entity->setCreateBy($user->getUserName());
                $empresa = $em->getRepository(Empresa::class)->find($user->getIdempresa());
                if ($empresa) {
                    $entity->setEmpresa($empresa);
                }
            }
            $errors = $validator->validate($entity);
            if ($errors->count() > 0) {
                return new JsonResponse(['msg' => 'Errores de validación', 'errors' => (string) $errors], 422);
            }
            $em->persist($entity);
            $em->flush();
            return new JsonResponse(['msg' => 'Proveedor creado', 'id' => $entity->getId()], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => 'Error interno', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAll(): array
    {
        $result = [];
        foreach ($this->findAll() as $p) {
            $result[] = [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'rif' => $p->getRif(),
                'email' => $p->getEmail(),
                'telefono' => $p->getTelefono(),
                'direccion' => $p->getDireccion(),
                'contacto' => $p->getContacto(),
            ];
        }
        return $result;
    }

    public function update(int $id, $data, $validator, $helper): JsonResponse
    {
        $entity = $this->find($id);
        if (!$entity) {
            return new JsonResponse(['msg' => 'Proveedor no encontrado'], 404);
        }
        $helper->setParametersToEntity($entity, $data);
        $this->getEntityManager()->flush();
        return new JsonResponse(['msg' => 'Proveedor actualizado', 'id' => $entity->getId()]);
    }
}
