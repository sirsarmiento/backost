<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Piezas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Piezas|null find($id, $lockMode = null, $lockVersion = null)
 * @method Piezas|null findOneBy(array $criteria, array $orderBy = null)
 * @method Piezas[]    findAll()
 * @method Piezas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PiezasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Piezas::class);
    }

    // /**
    //  * @return Piezas[] Returns an array of Piezas objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Piezas
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
