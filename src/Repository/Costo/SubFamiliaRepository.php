<?php

namespace App\Repository\Costo;

use App\Entity\Costo\SubFamilia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SubFamilia|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubFamilia|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubFamilia[]    findAll()
 * @method SubFamilia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubFamiliaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubFamilia::class);
    }

    // /**
    //  * @return SubFamilia[] Returns an array of SubFamilia objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SubFamilia
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
