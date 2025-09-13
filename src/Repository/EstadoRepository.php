<?php

namespace App\Repository;

use App\Entity\Estado;
use App\Dto\EstadoOutPutDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

Use App\Entity\User;
use App\Entity\Empresa;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use	Doctrine\ORM\Tools\Pagination\Paginator;
/**
 * @method Estado|null find($id, $lockMode = null, $lockVersion = null)
 * @method Estado|null findOneBy(array $criteria, array $orderBy = null)
 * @method Estado[]    findAll()
 * @method Estado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoRepository extends ServiceEntityRepository
{
    private $security;
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Estado::class);
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
        $dataestado=array();
        foreach($paginator as $clave=>$valor){
            $estadoDto =new EstadoOutPutDto();
            $estadoDto->id=$valor->getId();
            $estadoDto->nombre=$valor->getNombre();
            $estadoDto->pais=($valor->getPais()!=null)?array("id"=>$valor->getPais()->getId(),"Pais"=>$valor->getPais()->getNombre()):[];        
            $estadoDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Estatus"=>$valor->getStatus()->getDescripcion()):[];        
            $ciudades=[];
            foreach($valor->getCiudads()as $claveCiudades=>$valorCiudades){
                $ciudades[]=array("id"=>$valorCiudades->getId(),
                               "Nombre"=>$valorCiudades->getNombre());
            }    
            $estadoDto->ciudads=$ciudades;
            $dataestado[]=$estadoDto;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataestado);
 
    }

     /**
     * Lista Estado.
     */
    public function findList()
    {
        $data= $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $dataestado=array();
        foreach($data as $clave=>$valor){
            $estadoDto =new EstadoOutPutDto();
            $estadoDto->id=$valor->getId();
            $estadoDto->nombre=$valor->getNombre();
            $estadoDto->pais=($valor->getPais()!=null)?array("id"=>$valor->getPais()->getId(),"Pais"=>$valor->getPais()->getNombre()):[];        
            $estadoDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Estatus"=>$valor->getStatus()->getDescripcion()):[];        
            $ciudades=[];
            foreach($valor->getCiudads()as $claveCiudades=>$valorCiudades){
                $ciudades[]=array("id"=>$valorCiudades->getId(),
                               "Nombre"=>$valorCiudades->getNombre());
            }    
            $estadoDto->ciudads=$ciudades;
            $dataestado[]=$estadoDto;

        }
       return array("data"=>$dataestado);
 
    }


    /**
     * Buscar Estado.
     */

    public function findEstadoBy($id){
        $moduleData= $this->createQueryBuilder('a')
            ->Where('a.pais='.$id)
            ->orderBy('a.nombre', 'ASC')
            ->getQuery()
            ->getResult();
        if (count($moduleData)==0) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $dataopciones=array();
        $datacategoria=array();
        $dataestado=array();
        foreach($moduleData as $valor){
            
            $estadoDto =new EstadoOutPutDto();
            
            $estadoDto->id=$valor->getId();
            $estadoDto->nombre=$valor->getNombre();

        
            $estadoDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Estatus"=>$valor->getStatus()->getDescripcion()):[];        
            $ciudades=[];
            foreach($valor->getCiudads()as $claveCiudades=>$valorCiudades){
                $ciudades[]=array("id"=>$valorCiudades->getId(),
                               "Nombre"=>$valorCiudades->getNombre());
            }    
            $estadoDto->ciudads=$ciudades;



            $dataestado[]=$estadoDto;

        }
        return new JsonResponse($dataestado,200);  
    }

     /**
     * Create Estado.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new Estado(),$data);

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
     * Update Estado.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Estado::class)->find($id);
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
    //  * @return Estado[] Returns an array of Estado objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Estado
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
