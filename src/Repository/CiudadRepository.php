<?php

namespace App\Repository;

use App\Entity\Ciudad;
use App\Dto\CiudadOutPutDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

Use App\Entity\User;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use	Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Empresa;

/**
 * @method Ciudad|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ciudad|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ciudad[]    findAll()
 * @method Ciudad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiudadRepository extends ServiceEntityRepository
{
    private $security;
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Ciudad::class);
    }

    public function findAllPage($data){

        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }
       
        $query= $this->createQueryBuilder('a');
        $query->orderBy('a.id', 'ASC');
        if($data['word']!=null){
            $query->where("a.nombre like '%".$data['word']."%' ");
        }

        $query->orderBy('a.id', 'ASC');   
        $query->getQuery();

        $paginatorTotalCount = new Paginator($query);	
        $paginator = new Paginator($query);	
    	$paginator->getQuery()	
      	->setFirstResult($data['rowByPage'] *($data['page']-1))	
      	->setMaxResults($data['rowByPage']);	
        $dataUser=array();
        $hijos=[];
        $rolesUser=[];
        $dataciudad=array();
        foreach($paginator as $clave=>$valor){
            $ciudadDto =new CiudadOutPutDto();
            
            $ciudadDto->id=$valor->getId();
            $ciudadDto->nombre=$valor->getNombre();

            
            $ciudadDto->estado=($valor->getEstado()!=null)?array("id"=>$valor->getEstado()->getId(),"Descripcion"=>$valor->getEstado()->getNombre()):[];        

            $ciudadDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];        

            //$paisDto->status=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];        
            $dataciudad[]=$ciudadDto;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataciudad);
 
    }

    /**
     * Lista Ciudad.
     */
    public function findList()
    {
        $data= $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $dataciudad=array();
        foreach($data as $clave=>$valor){
            $ciudadDto =new CiudadOutPutDto();
            $ciudadDto->id=$valor->getId();
            $ciudadDto->nombre=$valor->getNombre();
            $ciudadDto->estado=($valor->getEstado()!=null)?array("id"=>$valor->getEstado()->getId(),"Descripcion"=>$valor->getEstado()->getNombre()):[];        
            $ciudadDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];        
            $dataciudad[]=$ciudadDto;
        }
       return array("data"=>$dataciudad);
 
    }

    /**
     * Buscar Estado.
     */

    public function findCiudadByciudad($id){
        $moduleData= $this->createQueryBuilder('a')
            ->Where('a.estado='.$id)
            ->orderBy('a.nombre', 'ASC')
            ->getQuery()
            ->getResult();
        if (count($moduleData)==0) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $dataopciones=array();
        $datacategoria=array();
        $dataciudad=array();
        foreach($moduleData as $valor){
            $ciudadDto =new CiudadOutPutDto();
            
            $ciudadDto->id=$valor->getId();
            $ciudadDto->nombre=$valor->getNombre();
            $ciudadDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];        

            //$paisDto->status=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];        
            $dataciudad[]=$ciudadDto;

        }
        return new JsonResponse($dataciudad,200);  
    }

     /**
     * Create Ciudad.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new Ciudad(),$data);

        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entity->setCreateBy($currentUser->getUserName());
            $entity->setCreateBy($currentUser->getUserName());

            //$entity->setIdStatus($entityManager->getRepository(Status::class)->find(1)); 
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getNombre()],200);
        }    
    }

    /**
     * Update Ciudad.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Ciudad::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $entity=$helper->setParametersToEntity($entity,$data);
        $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
        $entity->setUpdateBy($currentUser->getUserName());
        $entity->setUpdateAt(new \DateTime());
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return new JsonResponse($messages,500);
        }else{
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Actualizado: '.$entity->getId()],200);
        }

    }



    // /**
    //  * @return Ciudad[] Returns an array of Ciudad objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ciudad
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
