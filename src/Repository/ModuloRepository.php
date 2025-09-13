<?php

namespace App\Repository;
use App\Dto\ModuloOutPutDto;
use App\Dto\ModuloRolOutPutDto;
use App\Dto\WidgetsMenuOutPutDto;
use App\Entity\Modulo;
use App\Entity\User;
use App\Entity\Status;
use App\Entity\Rol;
use App\Dto\RolOutPutDto;
use App\Entity\ModuloRol;
use App\Dto\MenuOutPutDto;
use App\Dto\MenuGeneralOutPutDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Dto\ComponenteOutPutDto;
use App\Entity\Empresa;
use	Doctrine\ORM\Tools\Pagination\Paginator;
/**
 * @method Modulo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Modulo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Modulo[]    findAll()
 * @method Modulo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuloRepository extends ServiceEntityRepository
{
    private $security;


    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Modulo::class);
    }  

    public function findAllPage($data){
        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }
        $query= $this->createQueryBuilder('a');
        $query->orderBy('a.id', 'ASC');
        if($data['word']!=null){
            $query->where("a.nombre like '%".$data['word']."%' or a.descripcion like '%".$data['word']."%'");
        }
        $query->orderBy('a.id', 'ASC');     
        $query->getQuery();
        $dataRol=[];
        $paginatorTotalCount = new Paginator($query);	
        $paginator = new Paginator($query);	
    	$paginator->getQuery()	
      	->setFirstResult($data['rowByPage'] *($data['page']-1))	
      	->setMaxResults($data['rowByPage']);	
        $dataUser=array();
        $hijos=[];
        $rolesUser=[];
        foreach($paginator as $clave=>$valor){
            $moduloDto =new ModuloOutPutDto();
            $moduloDto->id=$valor->getId();
            $moduloDto->nombre=$valor->getNombre();
            $moduloDto->descripcion=$valor->getDescripcion();
            $moduloDto->icono=$valor->getIcono();
            $moduloDto->padre=$valor->getPadre()!=null?array("id"=>$valor->getPadre()->getId(),"Descripcion"=>$valor->getPadre()->getDescripcion()):null;
            $moduloDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];
            $moduloDto->tipoComponente=$valor->getTipoComponente()!=null?$valor->getTipoComponente():null;
            $hijos=[];
            foreach($valor->getHijo()as $claveHijo=>$valorHijo){
                $hijos[]=array("id"=>$valorHijo->getId(),"MenuHijo"=>$valorHijo->getNombre());
            }
            $moduloDto->hijo=$hijos;
            if($valor->getCreateAt()!=null){
                $moduloDto->createAt=$valor->getCreateAt()->format("d/m/Y");
            }    
            $moduloDto->updateBy=$valor->getUpdateBy();
            if($valor->getUpdateAt()!=null){           
                 $moduloDto->updateAt=$valor->getUpdateAt()->format("d/m/Y");
            }
            $moduloDto->createBy=$valor->getCreatedBy();
            $rolesUser=[];
            if($valor->getModulorol()!=null){
                foreach($valor->getModulorol()as $roles){
                    $rolesUser[]=array("rol"=>$roles->getRol()->getDescripcion());
                }
            }    
            $moduloDto->roles= $rolesUser;
            $dataRol[]=$moduloDto;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataRol);
 
    }

    public function findAllModuloRol($data){
        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }

        $query= $this->createQueryBuilder('a')
        ->join("App\Entity\ModuloRol","p");
        $query->orderBy('a.id', 'ASC');
        if($data['word']!=null){
            $query->where("a.nombre like '%".$data['word']."%' or a.descripcion like '%".$data['word']."%'");
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
        foreach($paginator as $clave=>$valor){
            $moduloDto =new ModuloRolOutPutDto();
            $moduloDto->id=$valor->getId();
            $moduloDto->idModulo=$valor->getModulo()->getId();
            $moduloDto->nombreModulo=$valor->getModulo()->getNombre();
            $moduloDto->tipoComponente=$valor->getModulo()->getTipoComponente()!=null?$valor->getTipoComponente():null;
            $rolesUser=[];
            if($valor->getModulo()->getModulorol()!=null){
                foreach($valor->getModulorol()as $roles){
                    $rolesUser[]=array("rol"=>$roles->getRol()->getDescripcion());
                }
            }    
            $moduloDto->rol= $valor->getModulo()->getRol()->getDescripcion();
            $arrayAuto=explode(",", $valor->getAutorizacion());
            foreach($arrayAuto as $cadenaSustituir){
                $cadenaSustituir=str_replace("]", "", $cadenaSustituir);
                $cadenaSustituir=str_replace("[", "", $cadenaSustituir);
                $cadenaSustituir=str_replace("\"", "", $cadenaSustituir);
                $autorizacion[]=array("permiso"=>$cadenaSustituir);
            }
            $moduloDto->autorizaciones=$autorizacion;
            $dataRol[]=$moduloDto;
            $autorizacion[]=[];
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
            $moduloDto =new ModuloOutPutDto();
            $moduloDto->id=$valor->getId();
            $moduloDto->nombre=$valor->getNombre();
            $moduloDto->descripcion=$valor->getDescripcion();
            $moduloDto->icono=$valor->getIcono();
            $moduloDto->padre=$valor->getPadre()!=null?array("id"=>$valor->getPadre()->getId(),"Nombre"=>$valor->getPadre()->getNombre()):null;
            $moduloDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];
            $moduloDto->tipoComponente=$valor->getTipoComponente()!=null?$valor->getTipoComponente():null;
            // if($valor->getCreateAt()!=null){
            //     $moduloDto->createAt=$valor->getCreateAt()->format("d/m/Y");
            // }    
            // $moduloDto->updateBy=$valor->getUpdateBy();
            // if($valor->getUpdateAt()!=null){           
            //     $moduloDto->updateAt=$valor->getUpdateAt()->format("d/m/Y");
            // }
            $moduloDto->orden=$valor->getOrden()!=null?$valor->getOrden():null;
            $moduloDto->path=$valor->getPath()!=null?$valor->getPath():null;
            $moduleRoles=$valor->getModulorol();
            foreach($moduleRoles as $clave){
                $arrayAuto=explode(",", $clave->getAutorizacion());
                foreach($arrayAuto as $cadenaSustituir){
                    $cadenaSustituir=str_replace("]", "", $cadenaSustituir);
                    $cadenaSustituir=str_replace("[", "", $cadenaSustituir);
                    $cadenaSustituir=str_replace("\"", "", $cadenaSustituir);
                    $autorizacion[]=array("permiso"=>$cadenaSustituir);
                }
            }
            $moduloDto->autorizaciones=$autorizacion;
            $autorizacion=[];
            $rolesParam["roles"]=[];
            if($valor->getModulorol()!=null){
                foreach($valor->getModulorol()as $roles){
                    $rolesParam["roles"][]=array("rol"=>array("id"=>$roles->getId(),"nombre"=>$roles->getRol()->getDescripcion()));
                }
            }   
            
            $hijosParam["hijos"]=[];
            if($valor->getHijo()!=null){
                foreach($valor->getHijo() as $hijo){
                    $hijosParam["hijos"][]=array("hijo"=>array("id"=>$hijo->getId(),"nombre"=>$hijo->getDescripcion()));
                }
            }
            $moduloDto->hijo=$hijosParam["hijos"];
            $moduloDto->roles=$rolesParam["roles"];
            $hijosParam=[];
            $rolesParam=[];
            $dataModulo=$moduloDto;
        }
        return $dataModulo;
 
    }



    public function getWidgetsMenu(){
        $moduloData= $this->createQueryBuilder('a')
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
        $dataModulo= array();
        foreach($moduloData as $clave=>$valor){
            $moduloDto =new WidgetsMenuOutPutDto();
            $moduloDto->idModulo=$valor->getId();
            $moduloDto->nombreModulo=$valor->getNombre();
            $moduloDto->tipoComponente=$valor->getTipoComponente()!=null?$valor->getTipoComponente():null;
            $dataModulo[]=$moduloDto;
        }
        return $dataModulo;
 
    }
    /**
     * Create Modulo.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new Modulo(),$data);
        $errors = $validator->validate($entity);
        if(is_null($data["padre"]) && !is_null($data["orden"])&& $data["tipoComponente"]=="Menu"){
            $moduloPadre= $this->getEntityManager()->createQueryBuilder();
            $modulos= $moduloPadre->select("p")
                ->from("App\Entity\Modulo","p")
                ->where('p.padre is null')
                ->andWhere("p.orden =".$data["orden"])
                ->andWhere("p.tipoComponente ='Menu'")
                ->getQuery()
                ->getResult();
            if($modulos){
                return new JsonResponse(['msg'=>'Existe un Menu principal con ese numero de orden'],409);
            }              
        }
        if($errors->count() > 0){
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return new JsonResponse($messages,409);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entity->setCreatedBy($currentUser->getUserName());
            $entity->setUpdateBy($currentUser->getUserName());
            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            $entity->setStatus($entityStatus );           
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            // $autorizacion=[];
            // foreach ($data["roles"] as $key => $value) {
            //     $entityRol = $entityManager->getRepository(Rol::class)->findBy(array("descripcion"=>$value["rol"]));          
            //     $entityModuloRol = new ModuloRol();
            //     if($entityRol!=null){
            //         $autorizacion=[];
            //         foreach($data["autorizacion"] as $clave=>$valor){
            //             $autorizacion[]= $valor['permiso'];            
            //         }
            //         $entityModuloRol->setAutorizacion(json_encode($autorizacion));
            //         $entityModuloRol->setModulo($entity);         
            //         $entityModuloRol->setRol($entityRol[0]);         
            //         $entityManager->persist($entityModuloRol);
            //         $entityManager->flush();            
            //     }
            // }
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getId()],200);
        }    
    }


    /**
     * Update Modulo.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Modulo::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }    
        if(is_null($data["padre"]) && !is_null($data["orden"])&& $data["tipoComponente"]=="Menu"){
            $moduloPadre= $this->getEntityManager()->createQueryBuilder();
            $modulos= $moduloPadre->select("p")
                ->from("App\Entity\Modulo","p")
                ->where('p.padre is null')
                ->andWhere("p.orden =".$data["orden"])
                ->andWhere("p.tipoComponente ='Menu'")
                ->andWhere("p.id !=".$id)
                ->getQuery()
                ->getResult();
            if($modulos){
                return new JsonResponse(['msg'=>'Existe un Menu principal con ese numero de orden'],409);
            }
                      
        }
        $entity=$helper->setParametersToEntity($entity,$data);
        $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
        $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
        $entity->setStatus($entityStatus );           
        $entity->setUpdateBy($currentUser->getUserName());
        $entity->setUpdateAt(new \DateTime());
        // $autorizacion=[];
        // $moduloRoles =$entity->getModulorol();
        // foreach($moduloRoles as $moduloRol){               
        //     $entityManager->remove($moduloRol);
        //     $entityManager->flush();
        // }
        // foreach ($data["roles"] as $key => $value) {
        //     $entityRol = $entityManager->getRepository(Rol::class)->findBy(array("descripcion"=>$value["rol"]));          
        //     $entityModuloRol = new ModuloRol();
        //     if($entityRol!=null){
        //         $autorizacion=[];
        //         foreach($data["autorizacion"] as $clave=>$valor){
        //             $autorizacion[]= $valor['permiso'];            
        //         }
        //         $entityModuloRol->setAutorizacion(json_encode($autorizacion));
        //         $entityModuloRol->setModulo($entity);         
        //         $entityModuloRol->setRol($entityRol[0]);         
        //         $entityManager->persist($entityModuloRol);
        //         $entityManager->flush();            
        //     }
        // }
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


    public function menu(){
            $menuDto =new MenuGeneralOutPutDto();
            $menu =$this->findModuloByRol();
            $menuDto->opcionesMenu=$menu;
            $dataMenu[]=$menuDto;
        return $dataMenu;
 
    }
 
    public function componente(){
        $componenteDto =new ComponenteOutPutDto();
        $componentes=$this->findModuloComponenteByRol();
        $componenteDto->componentes=$componentes;
        $dataComponentes[]=$componenteDto;
        return $dataComponentes;

    }


    public function findModuloByRol(){
        $entityManager = $this->getEntityManager();        
        $dataModules["opcionesMenu"]=[];
            $entity= $this->getEntityManager()->createQueryBuilder();
            $modulos= $entity->select("p")
                ->from("App\Entity\Modulo","p")
                ->where('p.padre is null')
                ->andWhere("p.status =1")
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
                        "id"=>$modulo->getId(),
                        "nombre"=>$modulo->getNombre(),
                        "isTitle"=>is_null($modulo->getPadre())?"true":"false",
                        "path"=>$modulo->getPath(),
                        "icon"=>$modulo->getIcono(),
                        "orden"=>$modulo->getOrden(), "permisos"=>$autorizacion, 
                        "hijos"=>$this->getMenuChild($modulo->getId()));
                        $autorizacion=[];
                     }   
                    $encontro=false;
                }    
        
        foreach ($dataModules["opcionesMenu"] as $key => $row) {
            $aux[$key] =$row["orden"];
        }
        if(count($dataModules["opcionesMenu"])>0){    
            array_multisort($aux, SORT_ASC, $dataModules["opcionesMenu"]);
        }
        return $dataModules["opcionesMenu"];  
    }

    private function getMenuChild($menuPadre){
        $hijos=[];
        $entity= $this->getEntityManager()->createQueryBuilder();
        $modulos= $entity->select("p")
            ->from("App\Entity\Modulo","p")
            ->where('p.padre ='.$menuPadre)
            ->andWhere("p.status =1")
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
            "id"=>$modulo->getId(),
            "orden"=>$modulo->getOrden(),
            "permisos"=>$autorizacion,
            "path"=>$modulo->getPath(),
            "icon"=>$modulo->getIcono(),
            "isTitle"=>is_null($modulo->getPadre())?"true":"false",
            "hijos"=>$this->getMenuChild($modulo->getId()));
            $autorizacion[]=[];
        }    
        return $hijos;
    }


    public function findModuloComponenteByRol(){
        $entityManager = $this->getEntityManager();        
        $dataModules["componentes"]=[];
            $entity= $this->getEntityManager()->createQueryBuilder();
            $modulos= $entity->select("p")
                ->from("App\Entity\Modulo","p")
                ->andWhere("p.status =1")
                ->andWhere("p.tipoComponente ='Widget'")
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
        
        foreach ($dataModules["componentes"] as $key => $row) {
            $aux[$key] =$row["orden"];
        }
        if(count($dataModules["componentes"])>0){
           array_multisort($aux, SORT_ASC, $dataModules["componentes"]);
        }
        return $dataModules["componentes"];  
    }


   public function delete($id,$validator): JsonResponse  
   {
       $entityManager = $this->getEntityManager();
       $entityModulo =$entityManager->getRepository(Modulo::class)->find($id);
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
           foreach($entityModulo->getHijo() as $hijo){
                $hijo->setStatus($entityStatus);
                $entityManager->flush();
           }
           return new JsonResponse(['msg'=>'Registro Eliminado: '.$entityModulo->getId()],200);
       }

   }    


}
