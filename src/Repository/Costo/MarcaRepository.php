<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Marca;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class MarcaRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Marca::class);
    }

    public function getAll(): array
    {
        $items = $this->findBy([], ['nombre' => 'ASC']);
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->toArray($item);
        }
        return $result;
    }

    public function post(array $data): JsonResponse
    {
        if (empty($data['nombre'])) {
            return new JsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
        }

        $em = $this->getEntityManager();
        $entity = new Marca();
        $entity->setNombre($data['nombre']);
        $this->setAudit($entity, true);
        $em->persist($entity);
        $em->flush();

        return new JsonResponse(['success' => true, 'id' => $entity->getId(), 'data' => $this->toArray($entity)], 201);
    }

    public function updateItem(int $id, array $data): JsonResponse
    {
        $entity = $this->find($id);
        if (!$entity) {
            return new JsonResponse(['success' => false, 'message' => 'Marca no encontrada'], 404);
        }

        if (isset($data['nombre'])) {
            $entity->setNombre($data['nombre']);
        }
        $this->setAudit($entity, false);
        $this->getEntityManager()->flush();

        return new JsonResponse(['success' => true, 'id' => $entity->getId(), 'data' => $this->toArray($entity)]);
    }

    public function deleteItem(int $id): JsonResponse
    {
        $entity = $this->find($id);
        if (!$entity) {
            return new JsonResponse(['success' => false, 'message' => 'Marca no encontrada'], 404);
        }
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
        return new JsonResponse(['success' => true]);
    }

    private function setAudit(Marca $entity, bool $isCreate): void
    {
        $user = $this->security->getUser();
        $em = $this->getEntityManager();
        $username = 'system';
        if ($user) {
            $dbUser = $em->getRepository(User::class)->find($user->getId());
            if ($dbUser) {
                $username = $dbUser->getUserName();
            }
        }
        if ($isCreate) {
            $entity->setCreateBy($username);
            $entity->setCreateAt(new \DateTime());
        } else {
            $entity->setUpdateBy($username);
            $entity->setUpdateAt(new \DateTime());
        }
    }

    public function toArray(Marca $item): array
    {
        return [
            'id' => $item->getId(),
            'nombre' => $item->getNombre(),
        ];
    }
}
