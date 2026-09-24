<?php

namespace App\Repository\Costo;

use App\Entity\Costo\MaterialCatalogo;
use App\Entity\Costo\Tecnologia;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class TecnologiaRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Tecnologia::class);
    }

    public function getAll(): array
    {
        $items = $this->findBy([], ['codigo' => 'ASC']);
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->toArray($item);
        }
        return $result;
    }

    public function post(array $data): JsonResponse
    {
        if (empty($data['codigo']) || empty($data['nombre'])) {
            return new JsonResponse(['success' => false, 'message' => 'Código y nombre son requeridos'], 400);
        }

        $em = $this->getEntityManager();
        $entity = new Tecnologia();
        $entity->setCodigo($data['codigo']);
        $entity->setNombre($data['nombre']);
        $this->setAudit($entity, true);
        $this->syncMateriales($entity, $data['materiales'] ?? [], $em);
        $em->persist($entity);
        $em->flush();

        return new JsonResponse(['success' => true, 'id' => $entity->getId(), 'data' => $this->toArray($entity)], 201);
    }

    public function updateItem(int $id, array $data): JsonResponse
    {
        $entity = $this->find($id);
        if (!$entity) {
            return new JsonResponse(['success' => false, 'message' => 'Tecnología no encontrada'], 404);
        }

        $em = $this->getEntityManager();
        if (isset($data['codigo'])) {
            $entity->setCodigo($data['codigo']);
        }
        if (isset($data['nombre'])) {
            $entity->setNombre($data['nombre']);
        }
        if (array_key_exists('materiales', $data)) {
            foreach ($entity->getMateriales()->toArray() as $material) {
                $entity->removeMaterial($material);
            }
            $this->syncMateriales($entity, $data['materiales'] ?? [], $em);
        }
        $this->setAudit($entity, false);
        $em->flush();

        return new JsonResponse(['success' => true, 'id' => $entity->getId(), 'data' => $this->toArray($entity)]);
    }

    public function deleteItem(int $id): JsonResponse
    {
        $entity = $this->find($id);
        if (!$entity) {
            return new JsonResponse(['success' => false, 'message' => 'Tecnología no encontrada'], 404);
        }
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
        return new JsonResponse(['success' => true]);
    }

    private function syncMateriales(Tecnologia $entity, array $materiales, $em): void
    {
        foreach ($materiales as $item) {
            $id = is_array($item) ? ($item['id'] ?? null) : $item;
            if (!$id) {
                continue;
            }
            $material = $em->getRepository(MaterialCatalogo::class)->find((int) $id);
            if ($material) {
                $entity->addMaterial($material);
            }
        }
    }

    private function setAudit(Tecnologia $entity, bool $isCreate): void
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

    public function toArray(Tecnologia $item): array
    {
        $materiales = [];
        foreach ($item->getMateriales() as $material) {
            $materiales[] = [
                'id' => $material->getId(),
                'codigo' => $material->getCodigo(),
                'nombre' => $material->getNombre(),
            ];
        }

        return [
            'id' => $item->getId(),
            'codigo' => $item->getCodigo(),
            'nombre' => $item->getNombre(),
            'materiales' => $materiales,
        ];
    }
}
