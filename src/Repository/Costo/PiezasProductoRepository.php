<?php

namespace App\Repository\Costo;

use App\Entity\Costo\PiezasProducto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PiezasProducto|null find($id, $lockMode = null, $lockVersion = null)
 * @method PiezasProducto|null findOneBy(array $criteria, array $orderBy = null)
 * @method PiezasProducto[]    findAll()
 * @method PiezasProducto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PiezasProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PiezasProducto::class);
    }
}
