<?php

namespace App\Repository;

use App\Entity\Gerencia;
use App\Entity\Empresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\GerenciaOutPutDto;
use Symfony\Component\Security\Core\Security;

/**
 * @method Gerencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gerencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gerencia[]    findAll()
 * @method Gerencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GerenciaRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Gerencia::class);
    }


    public function findList()
    {
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        $datagerencia=[];
        $data= $this->createQueryBuilder('c')
            ->where('c.IdStatus = 1')
            ->andwhere('c.idempresa ='.$empresa->getId())
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        foreach($data as $clave=>$valor){
            $gerenciaDto =new GerenciaOutPutDto();
            $gerenciaDto->id=$valor->getId();
            $gerenciaDto->nombre=$valor->getNombre();
            $gerenciaDto->IdStatus=($valor->getStatusId()!=null)?array("id"=>$valor->getStatusId()->getId(),"Descripcion"=>$valor->getStatusId()->getDescripcion()):[];        
            $gerenciaDto->empresa=array("idempresa"=> $empresa->getId(),"empresa"=> $empresa->getNombre());
            $datagerencia[]=$gerenciaDto;
        }
       return array("data"=>$datagerencia);
    }
}
