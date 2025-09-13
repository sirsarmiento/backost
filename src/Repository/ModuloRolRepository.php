<?php

namespace App\Repository;
use App\Dto\ModuloRolOutPutDto;
use App\Entity\ModuloRol;
use App\Entity\User;
use App\Entity\Rol;
use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Dto\ComponenteOutPutDto;
Use App\Entity\Empresa;


use	Doctrine\ORM\Tools\Pagination\Paginator;
/**
 * @method Modulo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Modulo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Modulo[]    findAll()
 * @method Modulo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuloRolRepository extends ServiceEntityRepository
{
    private $security;


    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, ModuloRol::class);
    }  

    public function findAllModuloRol($data){
        $entityManagerDefault = $this->getEntityManager();
        //$empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }

        /* $query= $this->createQueryBuilder('a')
        ->join("App\Entity\Modulo","p"); */
        /* $query= $this->createQueryBuilder('a')
        ->join("App\Entity\ModuloRol","p");
        $query->orderBy('a.id', 'ASC'); */


        $entityManagerDefault= $this->getEntityManager()->createQueryBuilder();
        $query = $entityManagerDefault->select("a,m,r");
        $query= $entityManagerDefault->from("App\Entity\ModuloRol","a");
        $query->leftJoin('a.modulo', 'm');  
        $query->leftJoin('a.rol', 'r');
        //$query->leftJoin('a.idempresa', 'e');
        
        if($data['word']!=null){
            $query->where("m.descripcion like '%".$data['word']."%'  or m.nombre like '%".$data['word']."%' or m.tipoComponente like '%".$data['word']."%' or r.descripcion like '%".$data['word']."%' or a.autorizacion like '%".$data['word']."%' ");
        }

        $query->orderBy('m.nombre', 'ASC');
        $dataRol=[];
        $query->getQuery();
        
        $paginatorTotalCount = new Paginator($query);	
        $paginator = new Paginator($query);	
    	$paginator->getQuery()	
      	->setFirstResult($data['rowByPage'] *($data['page']-1))	
      	->setMaxResults($data['rowByPage']);	
        foreach($paginator as $clave=>$valor){
            $moduloDto =new ModuloRolOutPutDto();
            $moduloDto->id=$valor->getId();
            $moduloDto->idModulo=$valor->getModulo()->getId();
            $moduloDto->Modulodescripcion=$valor->getModulo()->getDescripcion();
            $moduloDto->nombreModulo=$valor->getModulo()->getNombre()!=null?$valor->getModulo()->getNombre():null;
            $moduloDto->tipoComponente=$valor->getModulo()->getTipoComponente()!=null?$valor->getModulo()->getTipoComponente():null;
            $moduloDto->rol= $valor->getRol()!=null?$valor->getRol()->getDescripcion():null;
            $autorizacion=[];
            $arrayAuto=explode(",", $valor->getAutorizacion());
            $moduloDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];
            foreach($arrayAuto as $cadenaSustituir){
                $cadenaSustituir=str_replace("]", "", $cadenaSustituir);
                $cadenaSustituir=str_replace("[", "", $cadenaSustituir);
                $cadenaSustituir=str_replace("\"", "", $cadenaSustituir);

                //$autorizacion[]=array("permiso"=>$cadenaSustituir,"empresa"=>$valor->getIdempresa()->getNombre());
                $autorizacion[]=array("permiso"=>$cadenaSustituir);
            }
            $cadenaSustituir="";
            $moduloDto->autorizaciones=$autorizacion;
            //$moduloDto->empresa=($valor->getIdempresa()!=null)?array("id"=>$valor->getIdempresa()->getId(),"nombre"=>$valor->getIdempresa()->getNombre()):[];        
            $dataRol[]=$moduloDto;
            $autorizacion=[];
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataRol);
 
    }


    


    public function findById($id){
        $moduloData= $this->createQueryBuilder('a')
            ->andWhere('a.id='.$id)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
        $dataModulo= array();
        $autorizacion=[];
        foreach($moduloData as $clave=>$valor){
            $moduloDto =new ModuloRolOutPutDto();
            $moduloDto->id=$valor->getId();
            $moduloDto->idModulo=$valor->getModulo()->getId();
            $moduloDto->nombreModulo=$valor->getModulo()->getNombre();
            $moduloDto->tipoComponente=$valor->getModulo()->getTipoComponente()!=null?$valor->getModulo()->getTipoComponente():null;
            $moduloDto->rol= $valor->getRol()!=null?$valor->getRol()->getDescripcion():null;
            $arrayAuto=explode(",", $valor->getAutorizacion());
            $moduloDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];
            $autorizacion=[];
            foreach($arrayAuto as $cadenaSustituir){
                $cadenaSustituir=str_replace("]", "", $cadenaSustituir);
                $cadenaSustituir=str_replace("[", "", $cadenaSustituir);
                $cadenaSustituir=str_replace("\"", "", $cadenaSustituir);
                $autorizacion[]=array("permiso"=>$cadenaSustituir);
            }
            $moduloDto->autorizaciones=$autorizacion;
            $cadenaSustituir="";
            $dataModulo[]=$moduloDto;
            $autorizacion=[];
        }
        return $dataModulo;
 
    }


    /**
     * Create Modulo Rol.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new ModuloRol(),$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return new JsonResponse($messages,409);
        }else{
            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            $entity->setStatus($entityStatus );           
            $entityRol = $entityManager->getRepository(Rol::class)->findOneBy(array("descripcion"=>$data["rol"]));          
            $entity->setRol($entityRol);           
    
            foreach($data["autorizaciones"] as $clave=>$valor){
                $autorizacion[]= $valor['permiso'];            
            }
            $entity->setAutorizacion(json_encode($autorizacion));
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getId()],200);
        }    
    }

    /**
     * Update Modulo.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(ModuloRol::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }    
        $entity=$helper->setParametersToEntity($entity,$data);
        $entityStatus = $entityManager->getRepository(Status::class)->findOneById($data["statusId"]);          
        $entity->setStatus($entityStatus );           
        foreach($data["autorizaciones"] as $clave=>$valor){
            $autorizacion[]= $valor['permiso'];            
        }
        $entityRol = $entityManager->getRepository(Rol::class)->findOneBy(array("descripcion"=>$data["rol"]));          
        $entity->setRol($entityRol);           

        $entity->setAutorizacion(json_encode($autorizacion));
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

    /**
     * Update delete.
     */

    public function delete($id,$validator): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entityModulo =$entityManager->getRepository(ModuloRol::class)->find($id);
        if (!$entityModulo) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $entityStatus = $entityManager->getRepository(Status::class)->findOneBy(array("descripcion"=>"Inactivo"));          
        $entityModulo->setStatus($entityStatus);
        $entityManager->flush();
        $errors = $validator->validate($entityModulo);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],409);
        }else{
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Eliminado: '.$entityModulo->getId()],200);
        }
 
    }    
 
   
}
