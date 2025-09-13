<?php

namespace App\Repository;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\TipoCuentaEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use	Doctrine\ORM\Tools\Pagination\Paginator;
use App\Dto\TipoCuentaEmailOutPutDto;
use App\Entity\Empresa;

class TipoCuentaEmailRepository extends ServiceEntityRepository
{
    private $security;


    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, TipoCuentaEmail::class);
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
        $paginatorTotalCount = new Paginator($query);	
        $paginator = new Paginator($query);	
    	$paginator->getQuery()	
      	->setFirstResult($data['rowByPage'] *($data['page']-1))	
      	->setMaxResults($data['rowByPage']);	
        $dataTipoCuentas=array();
        foreach($paginator as $clave=>$valor){
            $tipoCuentaEmail =new TipoCuentaEmailOutPutDto();
            $tipoCuentaEmail->id=!is_null($valor->getId())?$valor->getId():null;
            $tipoCuentaEmail->nombre=!is_null($valor->getNombre())?$valor->getNombre():null;
            $tipoCuentaEmail->smtp=!is_null($valor->getSmtp())?$valor->getSmtp():null;
            $tipoCuentaEmail->imap=!is_null($valor->getImap())?$valor->getImap():null;
            $tipoCuentaEmail->pop3=!is_null($valor->getPop3())?$valor->getPop3():null;
            $dataTipoCuentas[]=$tipoCuentaEmail;
        }
       return array("count"=>count($paginatorTotalCount),"data"=>$dataTipoCuentas);
 
    }


    public function findList()
    {

        $data= $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $dataCuentaEmail=array();    
        foreach($data as $clave=>$valor){
            $tipoCuentaEmail =new TipoCuentaEmailOutPutDto();
            $tipoCuentaEmail->id=!is_null($valor->getId())?$valor->getId():null;
            $tipoCuentaEmail->nombre=!is_null($valor->getNombre())?$valor->getNombre():null;
            $dataCuentaEmail[]=$tipoCuentaEmail;
        }
       return array("data"=>$dataCuentaEmail);
    }


    
    /**
     * Create Tipo Cuenta.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new TipoCuentaEmail(),$data);
        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());
            if($empresa)
                $entity->setIdempresa($empresa);   
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getNombre()],200);
        }    
    }
    


}
