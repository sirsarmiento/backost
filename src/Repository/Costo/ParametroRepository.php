<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Parametro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Parametro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parametro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parametro[]    findAll()
 * @method Parametro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParametroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parametro::class);
    }

    public function save(Parametro $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Parametro $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByPerfil(int $perfilId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.perfil = :perfilId')
            ->setParameter('perfilId', $perfilId)
            ->getQuery()
            ->getResult();
    }
}
