<?php

namespace App\Repository;
use App\Dto\CorreoSubjectOutPutDto;
use App\Dto\UserOutPutDto;
use App\Dto\MenuOutPutDto;
use App\Dto\UserRolesOutPutDto;
use App\Entity\User;
use App\Entity\Telefono;
use App\Entity\Redes;
use App\Entity\Tiporedes;
use App\Entity\Status;
use App\Entity\Parametros;
use App\Entity\Pais;
use App\Entity\Estado;
use App\Entity\Ciudad;
use App\Entity\Rol;
use App\Entity\Token;
use App\Entity\Cargo;
use App\Entity\Gerencia;
use App\Entity\Coordinacion;
use App\Entity\CorreoSubject;
use App\Entity\Dependencia;
use App\Entity\Empresa;
use App\Entity\EstructuraOrganizativa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use	Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\Criteria;
/**
 * @method User[]    findAll()
 */
class UserRepository extends ServiceEntityRepository
{

    private $security;
    private $passwordEncoder;
    private $assetPackage;

    public function __construct(ManagerRegistry $registry,Security $security,
    UserPasswordEncoderInterface $passwordEncoder,Packages $assetPackage)
    {
        $this->assetPackage=$assetPackage;
        $this->security = $security;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($registry, User::class);
    }  

    /**
     * Create User.
     */
    public function post($data,$validator,$helper,$email): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $roles=[];

        $existeUser = $this->createQueryBuilder('p')
        ->where("p.email='".$data["email"]."'")->orWhere("p.numeroDocumento='".$data["numeroDocumento"]."'")->orWhere("p.username='".$data["email"]."'")->orderBy('p.id', 'ASC')->getQuery()->getResult();
        if(count($existeUser)>0){
            return new JsonResponse(['msg'=>"El email,username o el numero de cédula ya se encuentra registrado"],409);
        }

        //Sir. Comente porque estoy enviando un solo rol
        foreach($data["roles"] as $clave=>$valor){
                $roles[]= $valor['rol'];            
        }

        $data["roles"]=trim(json_encode($roles),'"');

        $entity=$helper->setParametersToEntity(new User(),$data);
        $psswd = $data["numeroDocumento"];

        $entity->setPassword($this->passwordEncoder->encodePassword(
            $entity,
            isset($psswd) ? $psswd : '123456'
        ));

        //$entity->setIdestructura($data["idestructura"]);

        $entity->setUsername($data["email"]);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityPais = $entityManager->getRepository(Pais::class)->findOneById($data["pais"]);          
            $entityEstado = $entityManager->getRepository(Estado::class)->findOneById($data["estado"]);          
            $entityCiudad = $entityManager->getRepository(Ciudad::class)->findOneById($data["ciudad"]);          
            if(isset($data["gerenciaId"])){
                $entityGerencia = $entityManager->getRepository(Gerencia::class)->findOneById($data["gerenciaId"]);          
                $entity->setIdGerencia($entityGerencia?$entityGerencia:null);
            }
            if(isset($data["coordinacionId"])){
                $entityCoordinacion = $entityManager->getRepository(Coordinacion::class)->findOneById($data["coordinacionId"]);          
                $entity->setIdCoordinacion($entityCoordinacion?$entityCoordinacion:null);

            }

            $entity->setPais($entityPais);
            $entity->setEstado($entityEstado);
            $entity->setCiudad($entityCiudad);
            $entity->setSexo($data["sexo"]);
            $entity->setDireccion($data["direccion"]);         
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entity->setCreateBy($currentUser->getUserName());
            $entity->setRoles(json_encode($data["roles"]));

            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());

            if($empresa)
                $entity->setIdempresa($empresa);
            $entityManager->persist($entity);
            $entityManager->flush();
            // foreach ($data["telefono"] as $key => $value) {
            //     $entityTelefono=new Telefono();
            //     $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            //     $entityTelefono->setIdUser($entity);
            //     $entityTelefono->setNumero($value["numero"]);   
            //     $entityTelefono->setCreateBy($currentUser->getUserName());
            //     $entityTelefono->setIdStatus($entityStatus); 
            //     $errors = $validator->validate($entityTelefono);
            //     if($errors->count() > 0){
            //         $errorsString = (string) $errors;
            //         return new JsonResponse(['msg'=>$errorsString],500);
            //     }else{
            //         $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            //         if($empresa)
            //            $entityTelefono->setIdempresa($empresa);
            //         $entityManager->persist($entityTelefono);
            //         $entityManager->flush();
            //     }    
            // }
            $email->enviocorreo(array("email"=>$entity->getEmail()),"Estimado Sr(a):".$entity->getPrimerNombre()."<br><br>"." Su usuario para acceder al sistema es ".$entity->getUsername()." y el password es ".$psswd);
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getUsername()],200);
        }    
    }

    public function findAllPage($data,$url){
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }
        $query= $this->createQueryBuilder('a');
        $query->orderBy('a.id', 'ASC');
        $query->Where("a.idStatus=1");
        
        if($data['word']!=null){
            $query->where("a.numeroDocumento like '%".$data['word']."%' or a.primerNombre like '%".$data['word']."%' or a.segundoNombre like '%".$data['word']."%' or a.username like '%".$data['word']."%'  and a.idempresa = ".$empresa->getId()."  "); 
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
 
            $userDto =new UserOutPutDto();
            $userDto->id=$valor->getId();
            $userDto->username=$valor->getUserName();
            $userDto->numeroDocumento=$valor->getNumeroDocumento();
            $userDto->tipoDocumentoIdentidad=$valor->getTipoDocumentoIdentidad();
            $userDto->primerNombre=$valor->getPrimerNombre(); 
            $userDto->segundoNombre=$valor->getSegundoNombre();
            $userDto->primerApellido= $valor->getPrimerApellido();
            $userDto->segundoApellido= $valor->getSegundoApellido();
            $userDto->fechaNacimiento=  $valor->getFechaNacimiento() == null ? '' : $valor->getFechaNacimiento()->format("Y-m-d");
            $userDto->email=$valor->getEmail();
            $userDto->cargo=($valor->getIdCargo()!=null)?array("id"=>$valor->getIdCargo()->getId(),"Descripcion"=>$valor->getIdCargo()->getDescripcion()):[];
            $userDto->idestructura=$valor->getIdestructura();

            //$userDto->Dependencia=($valor->getIdDependencia()!=null)?array("id"=>$valor->getIdDependencia()->getId(),"Descripcion"=>$valor->getIdDependencia()->getDescripcion()):[];
            //$userDto->Gerencia=($valor->getIdGerencia()!=null)?array("id"=>$valor->getIdGerencia()->getId(),"Nombre"=>$valor->getIdGerencia()->getNombre()):[];
            //$userDto->Coordinacion=($valor->getIdCoordinacion()!=null)?array("id"=>$valor->getIdCoordinacion()->getId(),"Nombre"=>$valor->getIdCoordinacion()->getNombre()):[];

            $idEstructura = $valor->getIdestructura();

            if ($idEstructura !== null) {
                $estructura = $entityManagerDefault
                    ->getRepository(EstructuraOrganizativa::class)
                    ->find($idEstructura);

                if ($estructura) {
                    $userDto->Dependencia = $estructura->getEstructuraOrganizativa();
                }
            }

            $userDto->pais=($valor->getPais()!=null)?array("id"=>$valor->getPais()->getId(),"Nombre"=>$valor->getPais()->getNombre()):[];
            $userDto->status=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];
            $userDto->estado=($valor->getEstado()!=null)?array("id"=>$valor->getEstado()->getId(),"Nombre"=>$valor->getEstado()->getNombre()):[];
            $userDto->ciudad=($valor->getCiudad()!=null)?array("id"=>$valor->getCiudad()->getId(),"Nombre"=>$valor->getCiudad()->getNombre()):[];
            $userDto->sexo=$valor->getSexo();
            $userDto->direccion=$valor->getDireccion();

            if(!is_null($valor->getFoto())){
                //$stringFoto=$url.'\fotos\\'.$valor->getFoto();
                $stringFoto=$url.$valor->getFoto();

                $stringFoto = filter_var($stringFoto, FILTER_SANITIZE_URL);
                $stringFoto = str_replace('\\', '', $stringFoto);
           }
            if($userDto->sexo=="M"){
                $userDto->foto= (is_null($valor->getFoto())) ? $this->assetPackage->getUrl($url.'\images\avatar_masculino.png'):$this->assetPackage->getUrl($stringFoto);
            }else{
                $userDto->foto= (is_null($valor->getFoto())) ? $this->assetPackage->getUrl($url.'\images\avatar_femenino.png'):$this->assetPackage->getUrl($stringFoto);                
            }
           
            if($valor->getCreateAt()!=null){
                $userDto->createAt=$valor->getCreateAt()->format("d/m/Y");
            }    
            $userDto->updateBy=$valor->getUpdateBy();
            if($valor->getUpdateAt()!=null){           
                 $userDto->updateAt=$valor->getUpdateAt()->format("d/m/Y");
            }
            $userDto->createBy=$valor->getCreateBy();
            foreach($valor->getTelefonos()as $telefonos){
                $telefonosUser[]=array("id"=>$telefonos->getId(),"numero"=>$telefonos->getNumero());
            }

            $userDto->telefonos=$telefonosUser;
      
            if($valor->getRoles()!=null){
                foreach($valor->getRoles()as $roles){
                    $rolesUser[]=array("rol"=>$roles);
                }
            }    

            $userDto->roles= $rolesUser;
            $rolesUser=[];
            $telefonosUser=[];
            $dataUser[]=$userDto;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataUser);
 
    }

    
     public function findById($id,$url){
        $entityManager = $this->getEntityManager();
        $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        $userData= $this->createQueryBuilder('a')
            ->andWhere('a.id='.$id)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
        $dataUser=array();
        $telefonosUser=[];
        foreach($userData as $clave=>$valor){
            $userDto =new UserOutPutDto();
            $userDto->id=$valor->getId();
            $userDto->username=$valor->getUserName();
            $userDto->numeroDocumento=$valor->getNumeroDocumento();
            $userDto->tipoDocumentoIdentidad=$valor->getTipoDocumentoIdentidad();
            $userDto->primerNombre=$valor->getPrimerNombre(); 
            $userDto->segundoNombre=$valor->getSegundoNombre();
            $userDto->primerApellido= $valor->getPrimerApellido();
            $userDto->segundoApellido= $valor->getSegundoApellido();
            $userDto->fechaNacimiento=$valor->getFechaNacimiento()?$valor->getFechaNacimiento()->format("Y-m-d"):null;
            $userDto->email=$valor->getEmail();
            $userDto->cargo=($valor->getIdCargo()!=null)?array("id"=>$valor->getIdCargo()->getId(),"Descripcion"=>$valor->getIdCargo()->getDescripcion()):[];
            $userDto->Dependencia=($valor->getIdDependencia()!=null)?array("id"=>$valor->getIdDependencia()->getId(),"Descripcion"=>$valor->getIdDependencia()->getDescripcion()):null;
            $userDto->Gerencia=($valor->getIdGerencia()!=null)?array("gerenciaId"=>$valor->getIdGerencia()->getId(),"nombreGerencia"=>$valor->getIdGerencia()->getNombre()):null;
            $userDto->Coordinacion=($valor->getIdCoordinacion()!=null)?array("coordinacionId"=>$valor->getIdCoordinacion()->getId(),"nombreCoordinacion"=>$valor->getIdCoordinacion()->getNombre()):null;
            $userDto->status=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];
            $userDto->pais=($valor->getPais()!=null)?array("id"=>$valor->getPais()->getId(),"Nombre"=>$valor->getPais()->getNombre()):[];
            $userDto->estado=($valor->getEstado()!=null)?array("id"=>$valor->getEstado()->getId(),"Nombre"=>$valor->getEstado()->getNombre()):[];
            $userDto->ciudad=($valor->getCiudad()!=null)?array("id"=>$valor->getCiudad()->getId(),"Nombre"=>$valor->getCiudad()->getNombre()):[];
            $userDto->sexo=$valor->getSexo();
            $userDto->direccion=$valor->getDireccion();
           if(!is_null($valor->getFoto())){
                //$stringFoto=$url.'\fotos\\'.$valor->getFoto();
                $stringFoto=$url.$valor->getFoto();
                $stringFoto = filter_var($stringFoto, FILTER_SANITIZE_URL);
                $stringFoto = str_replace('\\', '', $stringFoto);
           }
            if($userDto->sexo=="M"){
                $userDto->foto= (is_null($valor->getFoto())) ? $this->assetPackage->getUrl($url.'\images\avatar_masculino.png'):$this->assetPackage->getUrl($stringFoto);
            }else{
                $userDto->foto= (is_null($valor->getFoto())) ? $this->assetPackage->getUrl($url.'\images\avatar_femenino.png'):$this->assetPackage->getUrl($stringFoto);                
            }
            if($valor->getCreateAt()!=null){
                $userDto->createAt=$valor->getCreateAt()->format("d/m/Y");
            }    
            $userDto->updateBy=$valor->getUpdateBy();
            if($valor->getUpdateAt()!=null){           
                 $userDto->updateAt=$valor->getUpdateAt()->format("d/m/Y");
            }
            $userDto->createBy=$valor->getCreateBy();
            foreach($valor->getTelefonos()as $telefonos){
                $telefonosUser[]=array("id"=>$telefonos->getId(),"numero"=>$telefonos->getNumero());
            }
            $userDto->telefonos=$telefonosUser;
            $rolesParam["roles"]=[];
            if($valor->getRoles()!=null){
                foreach($valor->getRoles()as $roles){
                    $rolesUser[]=array("rol"=>$roles);
                    $rolesParam["roles"][]=array("rol"=>$roles);
                }
            }  

            $menu =$entityManager->getRepository(Rol::class)->findModuloByRol($rolesParam);

            $componentes=$entityManager->getRepository(Rol::class)->findModuloComponenteByRol($rolesParam);
            
            $userDto->opcionesMenu=$menu;

            $userDto->componentes=$componentes;
            $userDto->roles= $rolesUser;
            //$userDto->redes=$redesSociales; //Da error validar despues SIR
            $userDto->empresa=($empresa->getNombre()!=null)?array("id"=>$empresa->getId(),"Nombre"=>$empresa->getNombre(),"url_logo"=>$url.''.$empresa->getUrlLogo()):[];
            $rolesUser=[];
            $telefonosUser=[];
            $dataUser[]=$userDto;
        }
        return $dataUser;
 
    }



    public function findByCi($id,$url){
        $entityManager = $this->getEntityManager();
        $userData= $this->createQueryBuilder('a')
            ->andWhere('a.numeroDocumento='.$id)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
        $dataUser=array();
        $telefonosUser=[];
        foreach($userData as $clave=>$valor){
            $userDto =new UserOutPutDto();
            $userDto->id=$valor->getId();
            $userDto->username=$valor->getUserName();
            $userDto->numeroDocumento=$valor->getNumeroDocumento();
            $userDto->tipoDocumentoIdentidad=$valor->getTipoDocumentoIdentidad();
            $userDto->primerNombre=$valor->getPrimerNombre(); 
            $userDto->segundoNombre=$valor->getSegundoNombre();
            $userDto->primerApellido= $valor->getPrimerApellido();
            $userDto->segundoApellido= $valor->getSegundoApellido();
            $userDto->fechaNacimiento=$valor->getFechaNacimiento()?$valor->getFechaNacimiento()->format("Y-m-d"):null;
            $userDto->email=$valor->getEmail();
            $userDto->cargo=($valor->getIdCargo()!=null)?array("id"=>$valor->getIdCargo()->getId(),"Descripcion"=>$valor->getIdCargo()->getDescripcion()):[];
            $userDto->Dependencia=($valor->getIdDependencia()!=null)?array("id"=>$valor->getIdDependencia()->getId(),"Descripcion"=>$valor->getIdDependencia()->getDescripcion()):[];
            $userDto->Gerencia=($valor->getIdGerencia()!=null)?array("id"=>$valor->getIdGerencia()->getId(),"Nombre"=>$valor->getIdGerencia()->getNombre()):[];
            $userDto->Coordinacion=($valor->getIdCoordinacion()!=null)?array("id"=>$valor->getIdCoordinacion()->getId(),"Nombre"=>$valor->getIdCoordinacion()->getNombre()):[];
            $userDto->status=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];
            $userDto->pais=($valor->getPais()!=null)?array("id"=>$valor->getPais()->getId(),"Nombre"=>$valor->getPais()->getNombre()):[];
            $userDto->estado=($valor->getEstado()!=null)?array("id"=>$valor->getEstado()->getId(),"Nombre"=>$valor->getEstado()->getNombre()):[];
            $userDto->ciudad=($valor->getCiudad()!=null)?array("id"=>$valor->getCiudad()->getId(),"Nombre"=>$valor->getCiudad()->getNombre()):[];
            $userDto->sexo=$valor->getSexo();
            $userDto->direccion=$valor->getDireccion();
           if(!is_null($valor->getFoto())){
                //$stringFoto=$url.'\fotos\\'.$valor->getFoto();
                $stringFoto=$url.$valor->getFoto();
                $stringFoto = filter_var($stringFoto, FILTER_SANITIZE_URL);
                $stringFoto = str_replace('\\', '', $stringFoto);
           }
            if($userDto->sexo=="M"){
                $userDto->foto= (is_null($valor->getFoto())) ? $this->assetPackage->getUrl($url.'\images\avatar_masculino.png'):$this->assetPackage->getUrl($stringFoto);
            }else{
                $userDto->foto= (is_null($valor->getFoto())) ? $this->assetPackage->getUrl($url.'\images\avatar_femenino.png'):$this->assetPackage->getUrl($stringFoto);                
            }
            if($valor->getCreateAt()!=null){
                $userDto->createAt=$valor->getCreateAt()->format("d/m/Y");
            }    
            $userDto->updateBy=$valor->getUpdateBy();
            if($valor->getUpdateAt()!=null){           
                 $userDto->updateAt=$valor->getUpdateAt()->format("d/m/Y");
            }
            $userDto->createBy=$valor->getCreateBy();
            foreach($valor->getTelefonos()as $telefonos){
                $telefonosUser[]=array("id"=>$telefonos->getId(),"numero"=>$telefonos->getNumero());
            }

            $userDto->telefonos=$telefonosUser;
            $rolesParam["roles"]=[];
            if($valor->getRoles()!=null){
                foreach($valor->getRoles()as $roles){
                    $rolesUser[]=array("rol"=>$roles);
                    $rolesParam["roles"][]=array("rol"=>$roles);
                }
            }   

            $menu =$entityManager->getRepository(Rol::class)->findModuloByRol($rolesParam);

            $componentes=$entityManager->getRepository(Rol::class)->findModuloComponenteByRol($rolesParam);
            
            $userDto->opcionesMenu=$menu;

            $userDto->componentes=$componentes;
            $userDto->roles= $rolesUser;
            $userDto->redes=$redesSociales;
            $rolesUser=[];
            $telefonosUser=[];
            $dataUser[]=$userDto;
        }
        return $dataUser;
 
    }


    public function menu($id){
        $entityManager = $this->getEntityManager();
        $userData= $this->createQueryBuilder('a')
            ->andWhere('a.id='.$id)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
        $dataUser=array();
        foreach($userData as $clave=>$valor){
            $userDto =new MenuOutPutDto();
            $userDto->id=$valor->getId();
    
            $rolesParam["roles"]=[];
            if($valor->getRoles()!=null){
                foreach($valor->getRoles()as $roles){
                    $rolesUser[]=array("rol"=>$roles);
                    $rolesParam["roles"][]=array("rol"=>$roles);
                }
            }   
            $menu =$entityManager->getRepository(Rol::class)->findModuloByRol($rolesParam);
            $componentes=$entityManager->getRepository(Rol::class)->findModuloComponenteByRol($rolesParam);
            $userDto->opcionesMenu=$menu;
            $userDto->componentes=$componentes;
            $userDto->roles= $rolesUser;
            $rolesUser=[];
            $dataUser[]=$userDto;
        }
        return $dataUser;
 
    }


    public function findUserByRol($roles){
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        $roles =  explode("|",$roles["roles"]);
        $userRolesDto =new UserRolesOutPutDto();           
       $dataUser=array();
       $data=[];
        foreach($roles as $clave=>$valor){

            $entityManager = $this->getEntityManager();
            $dataUser= $this->createQueryBuilder('a')
                ->where("a.roles like '%".$valor."%'")
                ->andWhere('a.idempresa ='.$empresa->getId())
                ->orderBy('a.id', 'ASC')
                ->getQuery()
                ->getResult();
            foreach($dataUser as $valorUser){
                
                $data[]=array("nombre"=>$valorUser->getPrimerNombre(),
                              "apellido"=>$valorUser->getPrimerApellido(),
                              "email"=>$valorUser->getEmail(),
                              "id"=>$valorUser->getId(),
                              "role"=>$valor);
            }


        }
        $userRolesDto->data=$data;
        return $userRolesDto;
 
    }

    /**
     * Update User.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(User::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $user = $entityManager->getRepository(User::class)->findBy(array("email"=>$data["email"]));
        if($user!=null){
            $idcons = $user[0]->getId();
            if($idcons!=$id){
                return new JsonResponse(['msg'=>'El email que intenta actualizar ya existe verifique'],404);  
            }
        }
        $user = $entityManager->getRepository(User::class)->findBy(array("numeroDocumento"=>$data["numeroDocumento"]));
        if($user!=null){
            $idcons = $user[0]->getId();
            if($idcons!=$id){
                return new JsonResponse(['msg'=>'El numero de cedula que intenta actualizar ya existe verifique'],404);  
            }
        }
        foreach($data["roles"] as $clave=>$valor){
            $roles[]= $valor['rol'];            
        }
        $data["roles"]=trim(json_encode($roles),'"');
        $entity=$helper->setParametersToEntity($entity,$data);
        $entity->setUsername($data["email"]);
        $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
        $entityPais = $entityManager->getRepository(Pais::class)->findOneById($data["pais"]);          
        $entityEstado = $entityManager->getRepository(Estado::class)->findOneById($data["estado"]);          
        $entityCiudad = $entityManager->getRepository(Ciudad::class)->findOneById($data["ciudad"]);          
        $entity->setPais($entityPais);
        $entity->setEstado($entityEstado);
        $entity->setCiudad($entityCiudad);
        $entity->setSexo($data["sexo"]);
        $entity->setDireccion($data["direccion"]);         
        $entity->setUpdateBy($currentUser->getUserName());
        $entity->setUpdateAt(new \DateTime());
        $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);
        if(isset($data["gerenciaId"])){
            $entityGerencia = $entityManager->getRepository(Gerencia::class)->findOneById($data["gerenciaId"]);          
            $entity->setIdGerencia($entityGerencia?$entityGerencia:null);
        }
        if(isset($data["coordinacionId"])){
            $entityCoordinacion = $entityManager->getRepository(Coordinacion::class)->findOneById($data["coordinacionId"]);          
            $entity->setIdCoordinacion($entityCoordinacion?$entityCoordinacion:null);

        }
        $entityTelefonosDelete =$entityManager->getRepository(Telefono::class)->findBy([
            'idUser' => $id
        ]);
        foreach($entityTelefonosDelete as $telefonoDelete){               
            $entityManager->remove($telefonoDelete);
            $entityManager->flush();
        }
        foreach ($data["telefono"] as $key => $value){            
            $entityTelefono=new Telefono();
            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            $entityTelefono->setIdUser($entity);
            $entityTelefono->setNumero($value["numero"]);   
            $entityTelefono->setCreateBy($currentUser->getUserName());
            $entityTelefono->setIdStatus($entityStatus); 
            $errors = $validator->validate($entityTelefono);
            if($errors->count() > 0){
                $errorsString = (string) $errors;
                return new JsonResponse(['msg'=>$errorsString],500);
            }else{
                $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
                if($empresa)
                   $entityTelefono->setIdempresa($empresa);
                $entityManager->persist($entityTelefono);
                $entityManager->flush();
            }    
        }
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Actualizado: '.$entity->getUsername()],200);
        }

    }    

       /**
     * Update User.
     */
    public function editarPerfil($data,$id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(User::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
        $entityPais = $entityManager->getRepository(Pais::class)->findOneById($data["pais"]);          
        $entityEstado = $entityManager->getRepository(Estado::class)->findOneById($data["estado"]);          
        $entityCiudad = $entityManager->getRepository(Ciudad::class)->findOneById($data["ciudad"]);          
        $entity->setPais($entityPais);
        $entity->setEstado($entityEstado);
        $entity->setCiudad($entityCiudad);
        $entity->setDireccion($data["direccion"]);         
        $entity->setUpdateBy($currentUser->getUserName());
        $entity->setUpdateAt(new \DateTime());
        $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);
        $entityTelefonosDelete =$entityManager->getRepository(Telefono::class)->findBy([
            'idUser' => $id
        ]);
        $entityRedesDelete =$entityManager->getRepository(Redes::class)->findBy([
            'id_user_id' =>$id
        ]);

        foreach($entityTelefonosDelete as $telefonoDelete){               
            $entityManager->remove($telefonoDelete);
            $entityManager->flush();
        }

        foreach($entityRedesDelete as $redes){               
             $entityManager->remove($redes);
             $entityManager->flush();
        }


        foreach($data["redes"] as $clave=>$valor){
            $entityTiporedes = $entityManager->getRepository(Tiporedes::class)->findOneById($valor["tipo"]);          
            $redes = new Redes();
            $redes->setTiporedesId($entityTiporedes);
            $redes->setIdUserId($entity);
            $redes->setRed($valor["red"]);
            $errors = $validator->validate($redes);
            if($errors->count() > 0){
                $errorsString = (string) $errors;
                return new JsonResponse(['msg'=>$errorsString],500);
            }else{
                $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
                if($empresa)
                   $redes->setIdempresa($empresa);
                $entityManager->persist($redes);
                $entityManager->flush();
            }   
        }
        
        foreach ($data["telefono"] as $key => $value) {
            $entityTelefono=new Telefono();
            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
            $entityTelefono->setIdUser($entity);

            $entityTelefono->setNumero($value["numero"]);   
            $entityTelefono->setCreateBy($currentUser->getUserName());
            $entityTelefono->setIdStatus($entityStatus); 
            $errors = $validator->validate($entityTelefono);
            if($errors->count() > 0){
                $errorsString = (string) $errors;
                return new JsonResponse(['msg'=>$errorsString],500);
            }else{
                $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
                if($empresa)
                   $entityTelefono->setIdempresa($empresa);
                $entityManager->persist($entityTelefono);
                $entityManager->flush();
            }    
        }
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Actualizado: '.$entity->getUsername()],200);
        }

    }    


    /**
     * Update Photo User.
     */
    public function putPhoto($foto,$validator): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());

        $entity->setUpdateBy($entity->getUserName());
        $entity->setUpdateAt(new \DateTime());
        $entity->setFoto($foto);
        $entityManager->flush();
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Actualizado: '.$entity->getUsername()],200);
        }

    }    


        /**
     * Update Photo User.
     */
    public function loadDataUser($file,$validator): JsonResponse  
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $spreadsheet = $reader->load($file); 
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        $entityManager = $this->getEntityManager();
        $procesados=0;       
        $Noprocesados=array();
        $haveError=false;
        if (!empty($sheetData)) {
            $roles[]= 'ROLE_REGULAR';            
            for ($i=1; $i<count($sheetData); $i++) { //skipping first row
                //if($sheetData[$i][0]!=null){
                    $cedula = trim($sheetData[$i][4]);
                    $cedula=preg_replace('([^0-9])', '', $cedula);
                  
                    if(strtolower(trim($sheetData[$i][7]))!="m" and strtolower(trim($sheetData[$i][7])!="f")){
                        $errores[]=array("message"=>"El sexo es incorrecto: ".$sheetData[$i][7]);
                        $haveError=true;
                    } 
                    if(strtolower(trim($sheetData[$i][0])=="")){
                        $haveError=true;
                        $errores[]=array("message"=>"El nombre no puede estar en blanco");
                    } 
                    if(strtolower(trim($sheetData[$i][2])=="")){
                        $haveError=true;
                        $errores[]=array("message"=>"El apellido no puede estar en blanco");
                    } 
                    if(strtolower(trim($sheetData[$i][6])=="")){
                        $haveError=true;
                        $errores[]=array("message"=>"El email no puede estar en blanco");
                    } 
                    if(strtolower(trim($sheetData[$i][5])=="")){
                        $haveError=true;
                        $errores[]=array("message"=>"El cargo no puede estar en blanco");
                    } 

                    // $db = $this->createQueryBuilder('c');
                    // $entity =$db->select('c')
                    // ->where($db->expr()->orX()->addMultiple(
                    //     [
                    //         "c.email = '".$cedula."'",
                    //     ]
                    // ))
                    // ->getQuery()
                    // ->getResult();
                    $coordinacion="";
                    $gerencia="";
                    $coordinacion="";
                    if($sheetData[$i][8]!=""){    
                        $dependencia =$entityManager->getRepository(Dependencia::class)->findBy([
                                'id' => $sheetData[$i][8]
                        ]);
                        if (count($dependencia)==0) {
                            $haveError=true;
                            $errores[]=array("message"=>"La depedencia nro. ".$sheetData[$i][8]." no existe");
                        }    
                    }
                    if($sheetData[$i][9]!=""){    
                        $gerencia =$entityManager->getRepository(Gerencia::class)->findBy([
                                'id' => $sheetData[$i][9]
                        ]);
                        if (count($gerencia)==0) {
                            $haveError=true;
                            $errores[]=array("message"=>"La Gerencia nro. ".$sheetData[$i][9]." no existe");
                        }    
                    }
                    if($sheetData[$i][10]!=""){    
                        $coordinacion =$entityManager->getRepository(Coordinacion::class)->findBy([
                                'id' => $sheetData[$i][10]
                        ]);
                        if (count($coordinacion)==0) {
                            $haveError=true;
                            $errores[]=array("message"=>"La Coordinaci贸n nro. ".$sheetData[$i][10]." no existe");
                        }    
                    }
                    $entity =$entityManager->getRepository(User::class)->findBy([
                         'email' => $sheetData[$i][6]
                    ]);

                    if (count($entity)>0) {
                        $haveError=true;
                        $errores[]=array("message"=>"El email existe con el usuario ".$entity[0]->getPrimerNombre()." ".$entity[0]->getPrimerApellido()." ".$entity[0]->getId());
                    }    
                    $entity =$entityManager->getRepository(User::class)->findBy([
                        'numeroDocumento' => $sheetData[$i][4]
                   ]);
                    if (count($entity)>0) {                 
                        $haveError=true;
                        $errores[]=array("message"=>"La cedula Existe con el usuario: ".$entity[0]->getPrimerNombre()." ".$entity[0]->getPrimerApellido()." ".$entity[0]->getId());
                    }    

                    // $entity =$entityManager->getRepository(User::class)->findBy([
                    //     'email' => $sheetData[$i][6]
                    // ]);
                  
                        if (!$haveError) {
                            $entity=new User();
                            $nombre = preg_replace('([^A-Za-z])', '', trim($sheetData[$i][0]));
                            $segundoNombre=$sheetData[$i][1]!=null?preg_replace('([^A-Za-z])', '',trim($sheetData[$i][1])):null;
                            $apellido = $sheetData[$i][2]!=null?preg_replace('([^A-Za-z])', '', trim($sheetData[$i][2])):null;
                            $segundoApellido = $sheetData[$i][3]!=null?preg_replace('([^A-Za-z])', '', trim($sheetData[$i][3])):null;
                            
                            // if($sheetData[$i][5]=="Gerente"){
                            //     $cargo=10;
                            // }elseif($sheetData[$i][5]=="Gerente"){
                            //     $cargo=11;
                            // };
                            $email = trim($sheetData[$i][6]);
                            $sexo=trim($sheetData[$i][7]);
                            $data["roles"]=trim(json_encode($roles),'"');
                            $psswd = substr( md5(microtime()), 1, 8);
                            $entityPais = $entityManager->getRepository(Pais::class)->findOneById(1);          
                            $entityEstado = $entityManager->getRepository(Estado::class)->findOneById(1);          
                            $entityCargo = $entityManager->getRepository(Cargo::class)->findOneBy(array("descripcion"=>$sheetData[$i][5]));          
                            $entityCargo = $entityManager->getRepository(Cargo::class)->findOneById($sheetData[$i][5]);          
                 
                            $entityCiudad = $entityManager->getRepository(Ciudad::class)->findOneById(1);          
                            $entity->setPrimerNombre($nombre);
                            $entity->setSegundoNombre($segundoNombre);
                            $entity->setPrimerApellido($apellido);
                            $entity->setSegundoApellido($segundoApellido);
                            $entity->setSexo($sexo);
                            $entity->setIdCargo($entityCargo); 
                            $entity->setPais($entityPais);
                            $entity->setEstado($entityEstado);
                            $entity->setCiudad($entityCiudad);
                            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
                            if($empresa)
                               $entity->setIdempresa($empresa);
                            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
                            $entity->setCreateBy($currentUser->getUserName());
                            $entity->setRoles(json_encode($data["roles"]));
                            $entity->setNumeroDocumento($cedula);
                            $entity->setTipoDocumentoIdentidad("V");
                            if($dependencia!="")
                                $entity->setIdDependencia($dependencia[0]);
                            if($gerencia!="")
                                $entity->setIdGerencia($gerencia[0]);
                            if($coordinacion!="")
                                $entity->setIdCoordinacion($coordinacion[0]);
                            $entity->setPassword($this->passwordEncoder->encodePassword(
                                $entity,
                                isset($psswd) ? $cedula : '123456'
                            ));
                            $entity->setUsername(strtolower($email));
                            $entity->setEmail(strtolower($email));
                            $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
                            $entity->setIdStatus($entityStatus); 
            
                            $errors = $validator->validate($entity);
                            if($errors->count() > 0){
                                //$errorsString = (string) $errors;
                                $errores=array();
                                foreach($errors as $error){
                                    $errores[]=array("message"=>$error->getMessage());
                                }
                                $Noprocesados[]=array( "Nombre"=>$sheetData[$i][0],"email"=>$sheetData[$i][6],"errores"=>$errores);
 
                               // return new JsonResponse(['msg'=>$errorsString],500);
                            }else{
                                $entityManager->persist($entity);
                                $entityManager->flush();
                                $procesados++;
                            }
                            $dependencia="";
                            $gerencia="";
                            $coordinacion="";
         
                        }else{
                            $Noprocesados[]=array("Nombre"=>$sheetData[$i][0],"email"=>$sheetData[$i][6],"cedula"=>$cedula,"errores"=>$errores);
                        }
                        $haveError=false;
                        $errores=array();
                                
                    }
                //}    
            }
            return new JsonResponse(["count"=>count($sheetData),"CantidadRegistrosProcesados"=>$procesados,"UsuariosNoProcesados"=>$Noprocesados]);

    }    


    /**
     * Update Photo User.
     */
    public function validUserData($file,$validator): JsonResponse  
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $spreadsheet = $reader->load($file); 
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        $entityManager = $this->getEntityManager();
        $procesados=0;       
        $Noprocesados=array();
        $haveError=false;
        if (!empty($sheetData)) {
            $roles[]= 'ROLE_REGULAR';            
            for ($i=1; $i<count($sheetData); $i++) { //skipping first row
                //if($sheetData[$i][0]!=null){
                    $cedula = trim($sheetData[$i][2]);
                    $cedula=preg_replace('([^0-9])', '', $cedula);
                    $entity =$entityManager->getRepository(User::class)->findBy([
                        'numeroDocumento' =>$cedula
                   ]);
                    if (count($entity)==0) {                 
                        $procesados++;
                        $haveError=true;
                        $errores[]=array($cedula);
                    }    

                        // if (!$haveError) {
                        // }else{
                        //     $Noprocesados[]=array("Nombre"=>$sheetData[$i][1],"cedula"=>$cedula);
                        // }
                        $haveError=false;
                        //$errores=array();
                                
                    }
                //}    
            }
            return new JsonResponse(["count"=>$procesados,"UsuariosNoProcesados"=>$errores]);
    }    


   public function validaUsersbyEmail($data){
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());

        $entityManager = $this->getEntityManager();          
        $eventUsers= $this->getEntityManager()->createQueryBuilder();
        $usuariosRegistrados=array();
        $usuariosNoRegistrados=array();
        foreach($data["users"] as $item){
            $eventUsers= $this->getEntityManager()->createQueryBuilder();
            $user= $eventUsers->select("a")
            ->from("App\Entity\User","a")
            ->Where("a.email='".$item."'")
            ->andWhere('a.idempresa ='.$empresa->getId())
            ->getQuery()
            ->getResult();
            if(count($user)>0){
                $usuariosRegistrados[] =array("id"=>$user[0]->getId(),"cedula"=>$user[0]->getNumeroDocumento(),"correo"=>$user[0]->getEmail(),"fullname"=>$user[0]->getPrimerNombre()." ".$user[0]->getPrimerApellido());
            }else{
                $usuariosNoRegistrados[]=array($item);
            }           
        }
        return new JsonResponse(['data'=>array("usuariosregistrados"=>$usuariosRegistrados,"usuariosnoregistrados"=>$usuariosNoRegistrados)],200);
    }


    /**
     * Delete User.
     */
    public function delete($id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(User::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $entityStatus = $entityManager->getRepository(Status::class)->findOneById(2);          
        $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
        $entity->setUpdateBy($currentUser->getUserName());
        $entity->setUpdateAt(new \DateTime());
        $entity->setIdStatus($entityStatus);
        $entityManager->flush();
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Eliminado: '.$entity->getUsername()],200);
        }

    }    


    /**
     * RecoverPass User.
     */
    public function recoverPass($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new User(),$data);
        $entity->setPassword($this->passwordEncoder->encodePassword(
            $entity,
            isset($data['password']) ? $data['password'] : '123456'
        ));
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entity->setCreateBy($currentUser->getUserName());
            $entityManager->persist($entity);
            $entityManager->flush();
            foreach ($data["telefono"] as $key => $value) {

                $entityTelefono=new Telefono();
                $entityStatus = $entityManager->getRepository(Status::class)->findOneById(1);          
                $entityTelefono->setIdUser($entity);
                $entityTelefono->setNumero($value["numero"]);   
                $entityTelefono->setCreateBy($currentUser->getUserName());
                $entityTelefono->setIdStatus($entityStatus); 
                $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
                if($empresa)
                   $entityTelefono->setIdempresa($empresa);
                $errors = $validator->validate($entityTelefono);
                if($errors->count() > 0){
                    $errorsString = (string) $errors;
                    return new JsonResponse(['msg'=>$errorsString],500);
                }else{
                    $entityManager->persist($entityTelefono);
                    $entityManager->flush();
                }    
            }
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getUsername()],200);
        }    
    }


    public function changePassword($data,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $token =$entityManager->getRepository(Token::class)->findBy(array("codigo"=>$data["token"]));
        $expirationTokenDate=$token[0]->getFecha();

        if($token[0]->getSwusado()==0){
            $currentDate=new \DateTime();
            $tiempoExpiracionToken =0;                   
            $entityParametros =$entityManager->getRepository(Parametros::class)->findBy(array("nombre"=>"TiempoExpiracionToken"));
            if($entityParametros!=null){
                $tiempoExpiracionToken =$entityParametros[0]->getValue();   

                $entityManager = $this->getEntityManager();
                $entity =$entityManager->getRepository(Token::class)->find($token[0]->getId());
                if (!$entity) {
                    return new JsonResponse(['msg'=>'No existe el Token: '.$data["token"]],200);  
                }
                $entity->setSwusado(1);
                $entityManager->flush();

            }
            $expirationTokenDate->add(new \DateInterval('PT' . $tiempoExpiracionToken . 'M'));
            $diffInMinutes = iterator_count(new \DatePeriod($expirationTokenDate, new \DateInterval('PT1M'), $currentDate));        
            if($diffInMinutes>$tiempoExpiracionToken){
                return new JsonResponse(['msg'=>"El tiempo para cambiar la contraseña ha caducado"],409);
            } 
      }else{
            return new JsonResponse(['msg'=>"El token ya fue usado"],409);
      } 

 //       $user = $this->getDoctrine()->getRepository(User::class)->find($token->getIdUser()->getId());
        $entity=$token[0]->getIdUser();
        $entity->setPassword($this->passwordEncoder->encodePassword(
            $entity,
            isset($data['password']) ? $data['password'] : '123456'
        ));
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityManager->flush();
            return new JsonResponse(['msg'=>'El Password se cambio satisfactoriamente: '],200);
        }
    }
 

    public function changePasswordPerfil($data,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
        $currentUser->setPassword($this->passwordEncoder->encodePassword(
            $currentUser,
            isset($data['password']) ? $data['password'] : '123456'
        ));
        $errors = $validator->validate($currentUser);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityManager->flush();
            return new JsonResponse(['msg'=>'El Password se cambio satisfactoriamente: '],200);
        }
    }
 


    public function changePasswordTemp($data,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        
        $entity =$entityManager->getRepository(User::class)->find($data["id"]);       
        $entity->setPassword($this->passwordEncoder->encodePassword(
            $entity,
            $data['password']
        ));
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityManager->flush();
            return new JsonResponse(['msg'=>'El Password se cambio satisfactoriamente: '],200);
        }
    }

    public function findByList(){
        $entityManagerDefault = $this->getEntityManager();
        $empresa= $entityManagerDefault->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
        $data= $this->createQueryBuilder('u')
        ->where('u.idempresa ='.$empresa->getId())
        ->orderBy('u.id', 'ASC')
        ->getQuery()
        ->getResult();

        $datausers=array();
        foreach($data as $clave=>$valor){
            $userDto =new UserOutPutDto();
            $userDto->id=$valor->getId();
            $userDto->primerNombre=$valor->getPrimerNombre(); 
            $userDto->primerApellido= $valor->getPrimerApellido();
            $userDto->email=$valor->getEmail();
            $userDto->cargo=($valor->getIdCargo()!=null)?array("id"=>$valor->getIdCargo()->getId(),"Descripcion"=>$valor->getIdCargo()->getDescripcion()):[];
            $userDto->Dependencia=($valor->getIdDependencia()!=null)?array("id"=>$valor->getIdDependencia()->getId(),"Descripcion"=>$valor->getIdDependencia()->getDescripcion()):[];
            $datausers[]=$userDto;
        }
        return array("data"=>$datausers);
       

    }   


    public function findNacimientos($data,$url){
        $entityManager = $this->getEntityManager();
        $sql = " SELECT a.* FROM `actualiza_nacimiento` a 
        order by cedula asc ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result= $stmt->fetchAll();
        //$result= $stmt->executeQuery();
        $dataTotal=array();
        $colorprogress='';
        foreach($result as $claveResult=>$valorResult){
            $cedula= $valorResult["cedula"];
            $fecha= $valorResult["fecha"];
            $fechaBD = $fecha.' 00:00';
            $objFechaBD = date_create_from_format('d-m-Y H:i', $fechaBD);
            $dimF =  $objFechaBD->format("Y-m-d");
            $fechag = $dimF.' 00:00:00';

            $sql1 = " SELECT u.* FROM `user` u 
             where u.numero_documento='".$cedula."';";
            $conn1 = $this->getEntityManager()->getConnection();
            $stmt1 = $conn1->prepare($sql1);
            $stmt1->execute();
            $result1= $stmt1->fetchAll();
            if ($result1) {
                $sql2 = "update actualiza_nacimiento set sw=1 where cedula=".$cedula."";
                $conn2 = $this->getEntityManager()->getConnection();
                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute();
                $sql3 = "update user set fecha_nacimiento='".$fechag."' where numero_documento='".$cedula."' ";
                $conn3 = $this->getEntityManager()->getConnection();
                $stmt3 = $conn3->prepare($sql3);
                $stmt3->execute();
            }else{    
                $sql2 = "update actualiza_nacimiento set sw=0 where cedula=".$cedula."";
                $conn2 = $this->getEntityManager()->getConnection();
                $stmt2 = $conn2->prepare($sql2);
                $stmt2->execute();
            }
           // break;
        }
        return new JsonResponse(['msg'=>'Fin de la actualización satisfactoriamente: '],200);
    }

     /**
     * Status User.
     */
    public function findByListStatus(){
            $estatusact=0;
            $entityManager = $this->getEntityManager();
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $estatusact = $currentUser->getIdStatus()->getId();
            if($estatusact==2){
                return 2;
            }else{
                return 1;
            }


    }
    
     /**
     * Update User Empresa.
     */
    public function updateuserempresa($data,$validator): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());

        //$entity->setUpdateBy($entity->getUserName());

        $empresa= $entityManager->getRepository(Empresa::class)->find($data["idempresa"]);
        if($empresa)
            $entity->setIdempresa($empresa);

        $entity->setUpdateAt(new \DateTime());

        $entityManager->flush();
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Actualizado: '.$entity->getUsername()],200);
        }

    }    
    
    /**
     * Listar Correo subject.
     */
    public function findListSubject()
    {
        $entityManager = $this->getEntityManager();
        $dataSubject=[];
        $query = $entityManager->createQueryBuilder();
        $allAppointmentsQuery = $query->select('correosubject')
        ->from(CorreoSubject::class,'correosubject')
        ->addOrderBy('correosubject.id', 'ASC')
        ->getQuery();
        $queryult = $query->getQuery();
        $data =  $queryult->execute();
        $cont=0;
        foreach($data as $clave=>$valor){
          $subjectDto =new CorreoSubjectOutPutDto();
          $subjectDto->id=$valor->getId();
          $subjectDto->nombresubject=$valor->getNombreSubject();
          $dataSubject[]=$subjectDto;
      }
       return array("data"=>$dataSubject);
    }

    public function getAll(): array
    {
        $entityManager = $this->getEntityManager();
        $users = $this->createQueryBuilder('p')
            ->where('p.idStatus = :status')
            ->setParameter('status', 1)
            ->addOrderBy('p.primerNombre', 'ASC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id'    => $user->getId(),
                'fullName'   => $user->getPrimerNombre()." ".$user->getPrimerApellido(),
                'dependence'  => $user->getIdDependencia() == null ? 0 : $user->getIdDependencia()->getDescripcion(),
                'position' => $user->getIdCargo() == null ? 0 : $user->getIdCargo()->getDescripcion()
            ];
        }
        return $result;
    }
 
}

