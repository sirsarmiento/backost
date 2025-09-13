<?php

namespace App\Repository;

use App\Entity\Redes;
use App\Dto\RedesOutPutDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

Use App\Entity\User;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use	Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Redes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Redes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Redes[]    findAll()
 * @method Redes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RedesRepository extends ServiceEntityRepository
{
    private $security;
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Redes::class);
    }


        /**
     * Lista Redes.
     */
    public function findList()
    {
        $data= $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $datatiporedes=array();
        foreach($data as $clave=>$valor){
            $tiporedesDto =new RedesOutPutDto();
            $tiporedesDto->id=$valor->getId();
            //$tiporedesDto->nombre=$valor->getNombre();

            $tiporedesDto->tiporedes_id=($valor->getTiporedesId()!=null)?array("id"=>$valor->getTiporedesId()->getId(),"Nombre"=>$valor->getTiporedesId()->getNombre()):[];        

            $tiporedesDto->id_user_id=($valor->getIdUserId()!=null)?array("id"=>$valor->getIdUserId()->getId(),"User name"=>$valor->getIdUserId()->getUsername(),"Primer Nombre"=>$valor->getIdUserId()->getPrimerNombre(),"Segundo Nombre"=>$valor->getIdUserId()->getSegundoNombre(),"Primer Apellido"=>$valor->getIdUserId()->getPrimerApellido(),"Segundo Apellido"=>$valor->getIdUserId()->getSegundoApellido()):[];        


            $datatiporedes[]=$tiporedesDto;
        }
       return array("data"=>$datatiporedes);
 
    }



    // /**
    //  * @return Redes[] Returns an array of Redes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Redes
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
