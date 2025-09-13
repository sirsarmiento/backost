<?php

namespace App\Repository;
use App\Dto\CoordinacionOutPutDto;
use App\Entity\Coordinacion;
use App\Entity\Empresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Coordinacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coordinacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coordinacion[]    findAll()
 * @method Coordinacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoordinacionRepository extends ServiceEntityRepository
{
   
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Coordinacion::class);
    }


    public function findList()
    {
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        $datacoordinacion=[];
        $data= $this->createQueryBuilder('c')
            ->where('c.IdStatus = 1')
            ->andwhere('c.idempresa ='.$empresa->getId())
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        foreach($data as $clave=>$valor){
            $coordinacionDto =new CoordinacionOutPutDto();
            $coordinacionDto->id=$valor->getId();
            $coordinacionDto->nombre=$valor->getNombre();
            $coordinacionDto->IdStatus=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];        
            $coordinacionDto->empresa=array("idempresa"=> $empresa->getId(),"empresa"=> $empresa->getNombre());
            $datacoordinacion[]=$coordinacionDto;
        }
       return array("data"=>$datacoordinacion);
    }

}
