<?php

namespace App\Repository;

use App\Entity\Cargo;
use App\Entity\User;
use App\Entity\Status;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\CargoOutPutDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use	Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;
/**
 * @method Cargo|List find()
 */
class CargoRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Cargo::class);
    }

    public function findList()
    {
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        $data= $this->createQueryBuilder('c')
            ->where('c.IdStatus = 1')
            ->andWhere('c.idempresa ='.$empresa->getId())      
            ->orderBy('c.descripcion', 'ASC')
            ->getQuery()
            ->getResult()
        ;
         $dataCargo=array();
        foreach($data as $clave=>$valor){
            $cargoDto =new CargoOutPutDto();
            $cargoDto->id=$valor->getId();
            $cargoDto->descripcion=$valor->getDescripcion();
            $cargoDto->status=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];        
            $dataCargo[]=$cargoDto;
        }
       return array("data"=>$dataCargo);
    }

    
    /**
     * Create Cargo.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new Cargo(),$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return new JsonResponse($messages,409);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            $entity->setIdStatus($entityStatus );
            $entity->setUpdateBy($currentUser->getUsername());
            $entity->setCreateBy($currentUser->getUsername());
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getId()],200);
        }    
    }


    /**
     * Update Cargo.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Cargo::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $entity=$helper->setParametersToEntity($entity,$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return new JsonResponse($messages,409);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            $entity->setIdStatus($entityStatus );
            $entity->setUpdateBy($currentUser->getUsername());
            $entity->setCreateBy($currentUser->getUsername());
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Actualizado','id'=>$entity->getId()],200);
        }    
    }

    /**
     * Delete Cargo.
     */
    public function delete($id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Cargo::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $entityStatus = $entityManager->getRepository(Status::class)->findOneBy(array("descripcion"=>"Inactivo"));          
        $entity->setIdStatus($entityStatus);
        $entityManager->flush();
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],409);
        }else{
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Eliminado: '.$entity->getId()],200);
        }

    }    

    public function findAllPage($data){
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        $query= $this->createQueryBuilder('a');
        $query->Where('a.idempresa ='.$empresa->getId());
        $query->orderBy('a.descripcion', 'ASC');
        $query->getQuery();
        $paginatorTotalCount = new Paginator($query);	
        $paginator = new Paginator($query);	
        $dataUser=array();
        foreach($paginator as $clave=>$valor){
            $cargoDto =new CargoOutPutDto();
            $cargoDto->id=$valor->getId();
            $cargoDto->descripcion=$valor->getDescripcion();
            $cargoDto->nivel=$valor->getNivel()!=null?$valor->getNivel()->getNombre():"";            
            $dataUser[]=$cargoDto;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataUser);
 
    }

    public function findPagined($data){
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());

        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }
        $query= $this->createQueryBuilder('a');
        $query->orderBy('a.id', 'ASC');
        
        if($data['word']!=null){
            $query->where("a.descripcion like '%".$data['word']."%' and a.idempresa = ".$empresa->getId()." ");
        }else{
            $query->where("a.idempresa = ".$empresa->getId());
        }

        $query->orderBy('a.id', 'ASC');     
        $query->getQuery();

        $paginatorTotalCount = new Paginator($query);	
        $paginator = new Paginator($query);	
    	$paginator->getQuery()	
      	->setFirstResult($data['rowByPage'] *($data['page']-1))	
      	->setMaxResults($data['rowByPage']);	
        $dataUser=array();
        $telefonosUser=[];
        foreach($paginator as $clave=>$valor){
            $cargoDto =new CargoOutPutDto();
            $cargoDto->id=$valor->getId();
            $cargoDto->descripcion=$valor->getDescripcion();
            $cargoDto->nivel=$valor->getNivel()!=null?$valor->getNivel()->getNombre():"";            
            $dataUser[]=$cargoDto;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataUser);
 
    }


}
