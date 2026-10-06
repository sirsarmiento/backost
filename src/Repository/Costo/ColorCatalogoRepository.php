<?php

namespace App\Repository\Costo;

use App\Entity\Costo\ColorCatalogo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class ColorCatalogoRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, ColorCatalogo::class);
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
        $entity = new ColorCatalogo();
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
            return new JsonResponse(['success' => false, 'message' => 'Color no encontrado'], 404);
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
            return new JsonResponse(['success' => false, 'message' => 'Color no encontrado'], 404);
        }
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
        return new JsonResponse(['success' => true]);
    }

    private function setAudit(ColorCatalogo $entity, bool $isCreate): void
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

    public function toArray(ColorCatalogo $item): array
    {
        return [
            'id' => $item->getId(),
            'nombre' => $item->getNombre(),
        ];
    }
}
