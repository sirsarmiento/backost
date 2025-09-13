<?php

namespace App\Repository;

use App\Entity\Dependencia;
use App\Entity\Empresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\DependenciaOutPutDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

/**
 * @method Dependencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dependencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dependencia[]    findAll()
 * @method Dependencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DependenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Dependencia::class);
    }


   public function findList()
    {
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());

        $data= $this->createQueryBuilder('c')
            ->where('c.IdStatus = 1')
            ->andwhere('c.idempresa ='.$empresa->getId())
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $dataDependencia=[];
        foreach($data as $clave=>$valor){
            $dependenciaDto =new DependenciaOutPutDto();
            $dependenciaDto->id=$valor->getId();
            $dependenciaDto->descripcion=$valor->getDescripcion();
            $dependenciaDto->status=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];        
            
            $dependenciaDto->empresa=array("idempresa"=> $empresa->getId(),"empresa"=> $empresa->getNombre());

            $dataDependencia[]=$dependenciaDto;
        }
       return array("data"=>$dataDependencia);
    }
    
    /**
     * Create Dependencia.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new Dependencia(),$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entity->setCreateBy($currentUser->getUserName());
            $entity->setIdStatus($entityManager->getRepository(Status::class)->find(1)); 
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getUsername()],200);
        }    
    }



}
