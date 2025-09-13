<?php

namespace App\Repository;

use App\Entity\CuentaEmail;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\TipoCuentaEmail;
use App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use	Doctrine\ORM\Tools\Pagination\Paginator;
use App\Dto\CuentaEmailOutPutDto;
use App\Entity\Empresa;



/**
 * @method CuentaEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method CuentaEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method CuentaEmail[]    findAll()
 * @method CuentaEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CuentaEmailRepository extends ServiceEntityRepository
{
    private $security;
    
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, CuentaEmail::class);
    }


    public function findAllPage($data){
        if ($data['page'] != 0 && $data['page'] != 1) {
            $offset = ($data['page'] - 1) * $data['rowByPage'];
        }
        $query= $this->createQueryBuilder('a')->join("App\Entity\User","p");
        
        $query->orderBy('a.id', 'ASC');
        if($data['word']!=null){
            $query->where("a.nombre like '%".$data['word']."%' and p.id = ".$this->security->getUser()->getId());
        }
        $query->orderBy('a.id', 'ASC');     
        $query->getQuery();
        $paginatorTotalCount = new Paginator($query);	
        $paginator = new Paginator($query);	
    	$paginator->getQuery()	
      	->setFirstResult($data['rowByPage'] *($data['page']-1))	
      	->setMaxResults($data['rowByPage']);	
        $dataTipoCuentas=array();
        foreach($paginator as $clave=>$valor){
            $cuentaEmail =new CuentaEmailOutPutDto();
            $cuentaEmail->id=!is_null($valor->getId())?$valor->getId():null;
            $cuentaEmail->nombre=!is_null($valor->getNombre())?$valor->getNombre():null;
            $cuentaEmail->tipoCuenta=array("id"=>!is_null($valor->getTipoCuenta()->getId())?$valor->getTipoCuenta()->getId():null,"tipo"=>!is_null($valor->getTipoCuenta()->getNombre())?$valor->getTipoCuenta()->getNombre():null);
            $cuentaEmail->status=array("id"=>!is_null($valor->getStatus())?$valor->getStatus()->getId():null,"nombre"=>!is_null($valor->getStatus())?$valor->getStatus()->getDescripcion():null);
                     
            $dataTipoCuentas[]=$cuentaEmail;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataTipoCuentas);
 
    }


    /**
     * Create Cuenta.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new CuentaEmail(),$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errores=array();    
            foreach($errors as $error){
               $errores[] = array("error"=>$error->getMessage());
            }
            return new JsonResponse($errores,409);
        }else{  
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entity->setUser($currentUser);
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getNombre()],200);
        }    
    }


    public function findById($id){
        $cuentaData= $this->createQueryBuilder('a')
            ->andWhere('a.id='.$id)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
        $dataCuenta= array();
        foreach($cuentaData as $clave=>$valor){
            $cuentaDto =new CuentaEmailOutPutDto;
            $cuentaDto->id=$valor->getId();
            $cuentaDto->nombre=$valor->getNombre();
            $cuentaDto->tipoCuenta=array("id"=> $valor->getTipoCuenta()!=null?$valor->getTipoCuenta()->getId():null, "nombre"=>$valor->getTipoCuenta()!=null?$valor->getTipoCuenta()->getNombre():null);
            $cuentaDto->email= $valor->getEmail()!=null?$valor->getEmail():null;
            $cuentaDto->password= $valor->getPassword()!=null?$valor->getPassword():null;
            $cuentaDto->status=array(
                "id"=>!is_null($valor->getStatus())?$valor->getStatus()->getId():null,
                "nombre"=>!is_null($valor->getTipoCuenta())?$valor->getStatus()->getDescripcion():null);

            $dataModulo[]=$cuentaDto;
        }
        return $dataModulo; 
    }


    public function delete($id): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(CuentaEmail::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }
        $entityManager->remove($entity);
        $entityManager->flush();
        return new JsonResponse(['msg'=>'Registro Eliminado: '.$entity->getId()],200);
    
 
    }    

    
    public function findByUser($id){
        $cuentaData= $this->createQueryBuilder('a')
        ->join("App\Entity\User","p")
        ->join("App\Entity\TipoCuentaEmail","t")
            ->andWhere('p.id='.$id)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
        $dataCuenta= array();
        foreach($cuentaData as $clave=>$valor){
            $cuentaDto =new CuentaEmailOutPutDto;
            $cuentaDto->id=$valor->getId();
            $cuentaDto->nombre=$valor->getNombre();
            $cuentaDto->tipoCuenta=array(
            "id"=> $valor->getTipoCuenta()!=null?$valor->getTipoCuenta()->getId():null, 
            "nombre"=>$valor->getTipoCuenta()!=null?$valor->getTipoCuenta()->getNombre():null,
            "imap"=>$valor->getTipoCuenta()->getImap()!=null?$valor->getTipoCuenta()->getImap():null,
            "smtp"=>$valor->getTipoCuenta()->getSmtp()!=null?$valor->getTipoCuenta()->getSmtp():null,
            "pop3"=>$valor->getTipoCuenta()!=null?$valor->getTipoCuenta()->getPop3():null
        
            );
            $cuentaDto->email= $valor->getEmail()!=null?$valor->getEmail():null;
            $cuentaDto->password= $valor->getPassword()!=null?$valor->getPassword():null;
            $cuentaDto->status=array(
                            "id"=>!is_null($valor->getStatus())?$valor->getStatus()->getId():null,
                            "nombre"=>!is_null($valor->getTipoCuenta())?$valor->getStatus()->getDescripcion():null);
            $dataModulo[]=$cuentaDto;
        }
        return $dataModulo; 
    }

    public function findOneBuzonByIdAndIdUser($id,$idUser){
        $cuentaData= $this->createQueryBuilder('a')
        ->join("App\Entity\User","p")
        ->join("App\Entity\TipoCuentaEmail","t")
            ->andWhere('p.id='.$idUser)
            ->andWhere('a.id='.$id)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
        $dataModulo= array();
        foreach($cuentaData as $clave=>$valor){
            $cuentaDto =new CuentaEmailOutPutDto;
            $cuentaDto->id=$valor->getId();
            $cuentaDto->nombre=$valor->getNombre();
            $cuentaDto->tipoCuenta=array(
                "id"=> $valor->getTipoCuenta()!=null?$valor->getTipoCuenta()->getId():null, 
                "nombre"=>$valor->getTipoCuenta()!=null?$valor->getTipoCuenta()->getNombre():null,
                "imap"=>$valor->getTipoCuenta()->getImap()!=null?$valor->getTipoCuenta()->getImap():null,
                "smtp"=>$valor->getTipoCuenta()->getSmtp()!=null?$valor->getTipoCuenta()->getSmtp():null,
                "pop3"=>$valor->getTipoCuenta()!=null?$valor->getTipoCuenta()->getPop3():null
            );
            $cuentaDto->email= $valor->getEmail()!=null?$valor->getEmail():null;
            $cuentaDto->password= $valor->getPassword()!=null?$valor->getPassword():null;
            $dataModulo[]=$cuentaDto;
        }
        return $dataModulo; 
    }

        /**
     * Update Modulo.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(CuentaEmail::class)->find($id);
        if (!$entity) {
            return new JsonResponse(['msg'=>'No existen Registros con el id: '.$id],404);  
        }    
        $entity=$helper->setParametersToEntity($entity,$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errores=array();    
            foreach($errors as $error){
               $errores[] = array("error"=>$error->getMessage());
            }  
            return new JsonResponse($errores,409);
        }else{
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Actualizado: '.$entity->getId()],200);
        }

    }    


}
