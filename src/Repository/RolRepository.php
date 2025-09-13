<?php

namespace App\Repository;
use App\Entity\User;
use App\Entity\Status;
use App\Entity\Rol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\RolOutPutDto;
use App\Dto\ModuloOutPutDto;
Use App\Entity\Empresa;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use	Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Security;

/**
 * @method Rol|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rol|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rol[]    findAll()
 * @method Rol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Rol::class);
    }  


    public function findAllPage($data):JsonResponse{
        $dataRol=[];
        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }
        $query= $this->createQueryBuilder('a');
        $query->orderBy('a.id', 'ASC');
        if($data['word']!=null){
            $query->where("a.descripcion like '%".$data['word']."%'");
        }
        $query->orderBy('a.id', 'ASC');     
        $query->getQuery();
        $paginatorTotalCount = new Paginator($query);	
        $paginator = new Paginator($query);	
    	$paginator->getQuery()	
      	->setFirstResult($data['rowByPage'] *($data['page']-1))	
      	->setMaxResults($data['rowByPage']);	
        foreach($paginator as $clave=>$modulo){
            $RolOutPutDto =new RolOutPutDto();
            $RolOutPutDto->id=$modulo->getId();
            $RolOutPutDto->descripcion=$modulo->getDescripcion();
            $RolOutPutDto->id_status_id=array("statusId"=>$modulo->getIdStatus()->getId(),"statusLabel"=>$modulo->getIdStatus()->getDescripcion());
            $dataRol[]=$RolOutPutDto;
        }
        return New JsonResponse(["count"=>count($paginatorTotalCount),"data"=>$dataRol]);
 
    }




    public function findList()
    {
        $dataRol=[];
        $data= $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $eventorol=array();
        $cont=0;
        foreach($data as $clave=>$valor){
             $eventorol[$cont]=array("descripcion"=> $valor->getDescripcion());
             $cont++;
        }
        return array($eventorol);
    }


    /**
     * Create Rol.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new Rol(),$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return new JsonResponse($messages,409);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            // $entity->setCreatedBy($currentUser->getUserName());
            // $entity->setUpdateBy($currentUser->getUserName());
            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            $entity->setIdStatus($entityStatus );
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getId()],200);
        }    
    }


    /**
     * Update Rol.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Rol::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $entity=$helper->setParametersToEntity($entity,$data);
        $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
        $entityStatus = $entityManager->getRepository(Status::class)->findOneById($data["statusId"]);          
        $entity->setIdStatus($entityStatus);
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
     * Delete Rol.
     */
    public function delete($id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Rol::class)->find($id);
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


    public function findModuloComponenteByRol($param){
        $entityManager = $this->getEntityManager();        
        $dataModules["componentes"]=[];
        foreach($param["roles"] as $clave=>$valor){
            $entity= $this->getEntityManager()->createQueryBuilder();
            $modulos= $entity->select("p,q")
                ->from("App\Entity\Modulo","p")
                ->innerJoin('p.modulorol', 'q')
                ->innerJoin('q.rol', 'r')
                //->where('p.padre is null')
                ->where("r.descripcion ='".$valor["rol"]."'")
                ->andWhere("p.status =1")
                ->andWhere("q.status =1")
                ->andWhere("p.tipoComponente ='Widget'")
              //  ->orderBy('p.orden','Asc')
                ->getQuery()
                ->getResult();
                $encontro=false;
                foreach($modulos as $modulo){
                    if(isset($dataModules["componentes"])){
                        $encontro = in_array($modulo->getId(), $dataModules["componentes"]);
                        foreach($dataModules["componentes"] as $key=>$value){
                            $indice = array_search($modulo->getNombre(),$value);
                            if($indice){
                                $encontro =$indice;     
                            }
                        }
                    }
                     if(!$encontro){
                        $autorizacion=array();
                        $moduleRoles=$modulo->getModulorol();
                        foreach($moduleRoles as $clave){
                            $arrayAuto=explode(",", $clave->getAutorizacion());
                            foreach($arrayAuto as $cadenaSustituir){
                                $cadenaSustituir=str_replace("]", "", $cadenaSustituir);
                                $cadenaSustituir=str_replace("[", "", $cadenaSustituir);
                                $cadenaSustituir=str_replace("\"", "", $cadenaSustituir);
                                $autorizacion[]=array("permiso"=>$cadenaSustituir);
                            }
                        }
                        $dataModules["componentes"][]=array($modulo->getId()=>$modulo->getNombre(),
                        "icon"=>$modulo->getIcono(),
                        "orden"=>$modulo->getOrden(), 
                        "permisos"=>$autorizacion);
                     }   
                    $encontro=false;
                }    
        }
        foreach ($dataModules["componentes"] as $key => $row) {
            $aux[$key] =$row["orden"];
        }
        if(count($dataModules["componentes"])>0){
           array_multisort($aux, SORT_ASC, $dataModules["componentes"]);
        }
        return $dataModules["componentes"];  
    }



    public function findModuloByRol($param){
        $entityManager = $this->getEntityManager();        
        $dataModules["opcionesMenu"]=[];
        foreach($param["roles"] as $clave=>$valor){
            $entity= $this->getEntityManager()->createQueryBuilder();
            $modulos= $entity->select("p,q")
                ->from("App\Entity\Modulo","p")
                ->innerJoin('p.modulorol', 'q')
                ->innerJoin('q.rol', 'r')
                ->where('p.padre is null')
                ->andWhere("r.descripcion ='".$valor["rol"]."'")
                ->andWhere("p.status =1")
                ->andWhere("q.status =1")
                ->andWhere("p.tipoComponente ='Menu'")
                ->orderBy('p.orden','Asc')
                ->getQuery()
                ->getResult();
                $encontro=false;
                foreach($modulos as $modulo){
                    if(isset($dataModules["opcionesMenu"])){
                        foreach($dataModules["opcionesMenu"] as $key=>$value){
                            $indice = array_search($modulo->getNombre(),$value);
                            if($indice){
                                $encontro =$indice;     
                            }
                        }
                    }
                     if(!$encontro){
                        $autorizacion=array();
                        $moduleRoles=$modulo->getModulorol();
                        foreach($moduleRoles as $clave){
                            $arrayAuto=explode(",", $clave->getAutorizacion());
                            foreach($arrayAuto as $cadenaSustituir){
                                $cadenaSustituir=str_replace("]", "", $cadenaSustituir);
                                $cadenaSustituir=str_replace("[", "", $cadenaSustituir);
                                $cadenaSustituir=str_replace("\"", "", $cadenaSustituir);
                                $autorizacion[]=array("permiso"=>$cadenaSustituir);
                            }
                        }
                        $dataModules["opcionesMenu"][]=array(
                        "nombre"=>$modulo->getNombre(),
                        "isTitle"=>is_null($modulo->getPadre())?"true":"false",
                        "path"=>$modulo->getPath(),
                        "icon"=>$modulo->getIcono(),
                        "orden"=>$modulo->getOrden(), "permisos"=>$autorizacion, 
                        "hijos"=>$this->getMenuChild($modulo->getId(),$valor["rol"]));
                     }   
                    $encontro=false;
                }    
        }
        foreach ($dataModules["opcionesMenu"] as $key => $row) {
            $aux[$key] =$row["orden"];
        }
        if(count($dataModules["opcionesMenu"])>0){    
            array_multisort($aux, SORT_ASC, $dataModules["opcionesMenu"]);
        }
        return $dataModules["opcionesMenu"];  
    }

    private function getMenuChild($menuPadre,$role){
        $hijos=[];
        $entity= $this->getEntityManager()->createQueryBuilder();
        $modulos= $entity->select("p")
            ->from("App\Entity\Modulo","p")
            ->innerJoin('p.modulorol', 'q')
            ->innerJoin('q.rol', 'r')
            ->where('p.padre ='.$menuPadre)
            ->andWhere("r.descripcion ='".$role."'")
            ->andWhere("p.status =1")
            ->andWhere("q.status =1")
            ->andWhere("p.tipoComponente ='Menu'")
            ->orderBy('p.orden','Asc')
            ->getQuery()
            ->getResult();
        foreach($modulos as $modulo){
            $autorizacion=array();
            $moduleRoles=$modulo->getModulorol();
            foreach($moduleRoles as $clave){
                $arrayAuto=explode(",", $clave->getAutorizacion());
                foreach($arrayAuto as $cadenaSustituir){
                    $cadenaSustituir=str_replace("]", "", $cadenaSustituir);
                    $cadenaSustituir=str_replace("[", "", $cadenaSustituir);
                    $cadenaSustituir=str_replace("\"", "", $cadenaSustituir);
                    $autorizacion[]=array("permiso"=>$cadenaSustituir);
                }
            }
            $hijos["MenuChild"][]=array("menu"=>$modulo->getNombre(),
            "orden"=>$modulo->getOrden(),
            "permisos"=>$autorizacion,
            "path"=>$modulo->getPath(),
            "icon"=>$modulo->getIcono(),
            "isTitle"=>is_null($modulo->getPadre())?"true":"false",
            "hijos"=>$this->getMenuChild($modulo->getId(),$role));
        }    
        return $hijos;
    }
    
    
    public function Roleslist()
    {
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        $dataRol=[];
        $data= $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $eventorol=array();
        $cont=0;
        foreach($data as $clave=>$valor){
            $idemp=$valor->getIdempresa();
            $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($idemp);
            $eventorol[$cont]=array("descripcion"=> $valor->getDescripcion(),"idempresa"=> $empresa->getId(),"empresa"=> $empresa->getNombre()
            );
             $cont++;
        }
        return array($eventorol);
    }

}
