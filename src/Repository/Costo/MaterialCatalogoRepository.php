<?php

namespace App\Repository\Costo;

use App\Entity\Costo\MaterialCatalogo;
use App\Entity\Costo\Tecnologia;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class MaterialCatalogoRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, MaterialCatalogo::class);
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
        $entity = new MaterialCatalogo();
        $entity->setCodigo($data['codigo']);
        $entity->setNombre($data['nombre']);
        $this->setAudit($entity, true);
        $this->syncTecnologias($entity, $data['tecnologias'] ?? [], $em);
        $em->persist($entity);
        $em->flush();

        return new JsonResponse(['success' => true, 'id' => $entity->getId(), 'data' => $this->toArray($entity)], 201);
    }

    public function updateItem(int $id, array $data): JsonResponse
    {
        $entity = $this->find($id);
        if (!$entity) {
            return new JsonResponse(['success' => false, 'message' => 'Material no encontrado'], 404);
        }

        $em = $this->getEntityManager();
        if (isset($data['codigo'])) {
            $entity->setCodigo($data['codigo']);
        }
        if (isset($data['nombre'])) {
            $entity->setNombre($data['nombre']);
        }
        if (array_key_exists('tecnologias', $data)) {
            foreach ($entity->getTecnologias()->toArray() as $tecnologia) {
                $entity->removeTecnologia($tecnologia);
            }
            $this->syncTecnologias($entity, $data['tecnologias'] ?? [], $em);
        }
        $this->setAudit($entity, false);
        $em->flush();

        return new JsonResponse(['success' => true, 'id' => $entity->getId(), 'data' => $this->toArray($entity)]);
    }

    public function deleteItem(int $id): JsonResponse
    {
        $entity = $this->find($id);
        if (!$entity) {
            return new JsonResponse(['success' => false, 'message' => 'Material no encontrado'], 404);
        }
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
        return new JsonResponse(['success' => true]);
    }

    private function syncTecnologias(MaterialCatalogo $entity, array $tecnologias, $em): void
    {
        foreach ($tecnologias as $item) {
            $id = is_array($item) ? ($item['id'] ?? null) : $item;
            if (!$id) {
                continue;
            }
            $tecnologia = $em->getRepository(Tecnologia::class)->find((int) $id);
            if ($tecnologia) {
                $entity->addTecnologia($tecnologia);
            }
        }
    }

    private function setAudit(MaterialCatalogo $entity, bool $isCreate): void
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

    public function toArray(MaterialCatalogo $item): array
    {
        $tecnologias = [];
        foreach ($item->getTecnologias() as $tecnologia) {
            $tecnologias[] = [
                'id' => $tecnologia->getId(),
                'codigo' => $tecnologia->getCodigo(),
                'nombre' => $tecnologia->getNombre(),
            ];
        }

        return [
            'id' => $item->getId(),
            'codigo' => $item->getCodigo(),
            'nombre' => $item->getNombre(),
            'tecnologias' => $tecnologias,
        ];
    }
}
