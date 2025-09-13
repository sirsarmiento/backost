<?php

namespace App\Repository;

use App\Entity\Nivel;
use App\Entity\User;
use App\Entity\Status;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\NivelOutPutDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use	Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Security;
use App\Entity\Empresa;


/**
 * @method Nivel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nivel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nivel[]    findAll()
 * @method Nivel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NivelRepository extends ServiceEntityRepository
{   
    
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Nivel::class);
    }

    public function findList()
    {

        $data= $this->createQueryBuilder('c')
            ->where('c.status = 1')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        foreach($data as $clave=>$valor){
            $nivelDto =new NivelOutPutDto();
            $nivelDto->id=$valor->getId();
            $nivelDto->nombre=$valor->getNombre();
            $nivelDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];        
            $dataCargo[]=$nivelDto;
        }
       return array("data"=>$dataCargo);
    }

    
    /**
     * Create.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new Nivel(),$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return new JsonResponse($messages,409);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            $entity->setStatus($entityStatus);
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
     * Update.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Nivel::class)->find($id);
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
            $entity->setStatus($entityStatus );
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
     * Delete.
     */
    public function delete($id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Nivel::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $entityStatus = $entityManager->getRepository(Status::class)->findOneBy(array("descripcion"=>"Inactivo"));          
        $entity->setStatus($entityStatus);
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
        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }
        $query= $this->createQueryBuilder('a');
        $query->orderBy('a.id', 'ASC');
        if($data['word']!=null){
            $query->where("a.nombre like '%".$data['word']."%'");
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
            $nivelDto =new NivelOutPutDto();
            $nivelDto->id=$valor->getId();
            $nivelDto->nombre=$valor->getNombre();
            $dataUser[]=$nivelDto;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataUser);
 
    }

}
