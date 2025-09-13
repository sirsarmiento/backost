<?php

namespace App\Repository;
use App\Dto\StatusOutPutDto;
use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Status|null find($id, $lockMode = null, $lockVersion = null)
 * @method Status|null findOneBy(array $criteria, array $orderBy = null)
 * @method Status[]    findAll()
 * @method Status[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function findList()
    {

        $data= $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        foreach($data as $clave=>$valor){
            $statusDto =new StatusOutPutDto();
            $statusDto->id=$valor->getId();
            $statusDto->descripcion=$valor->getDescripcion();
            $datastatus[]=$statusDto;
        }
       return array("data"=>$datastatus);
 



    }
}
