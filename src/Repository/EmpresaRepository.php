<?php

namespace App\Repository;

use App\Entity\Empresa;
use App\Dto\EmpresaOutPutDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

Use App\Entity\User;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use	Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Empresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Empresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Empresa[]    findAll()
 * @method Empresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaRepository extends ServiceEntityRepository
{
    private $security;
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Empresa::class);
    }

    /**
     * Lista Empresa.
     */
    public function findList()
    {
        $data= $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        $datapais=array();
        foreach($data as $clave=>$valor){
            $paisDto =new EmpresaOutPutDto();
            $paisDto->id=$valor->getId();
            $paisDto->nombre=$valor->getNombre();
            $paisDto->status=($valor->getStatus()!=null)?array("id"=>$valor->getStatus()->getId(),"Descripcion"=>$valor->getStatus()->getDescripcion()):[];        
            //$paisDto->status=($valor->getIdStatus()!=null)?array("id"=>$valor->getIdStatus()->getId(),"Descripcion"=>$valor->getIdStatus()->getDescripcion()):[];        
            $datapais[]=$paisDto;
        }
       return array("data"=>$datapais);
 
    } 

     /**
     * Create Empresa.
     */
    public function post($data,$validator,$helper): JsonResponse  {
        $entityManager = $this->getEntityManager();
        $entity=$helper->setParametersToEntity(new Empresa(),$data);

        $errors = $validator->validate($entity);
        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new JsonResponse(['msg'=>$errorsString],500);
        }else{
            $currentUser =$entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $entity->setCreateBy($currentUser->getUserName());
            $entity->setCreateBy($currentUser->getUserName());

            //$entity->setIdStatus($entityManager->getRepository(Status::class)->find(1)); 
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Creado','id'=>$entity->getNombre()],200);
        }    
    }
 
    /**
     * Update Empresa.
     */
    public function put($data,$id,$validator,$helper): JsonResponse  
    {
        $entityManager = $this->getEntityManager();
        $entity =$entityManager->getRepository(Empresa::class)->find($id);
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
            $entityManager->persist($entity);
            $entityManager->flush();
            return new JsonResponse(['msg'=>'Registro Actualizado: '.$entity->getId()],200);
        }

    }

    public function resumen(): array
    {
        $entityManager = $this->getEntityManager();
        $empresa= $entityManager->getRepository(Empresa::class)->find($this->security->getUser()->getIdempresa());

        $resumen = [
            'processes' => $this->countByEmpresa($entityManager, 'App\Entity\Riesgo\Proceso', $empresa),
            'risks'     => $this->countByEmpresa($entityManager, 'App\Entity\Riesgo\Riesgo', $empresa),
            'controls'  => $this->countByEmpresa($entityManager, 'App\Entity\Riesgo\Control', $empresa),
            'events'    => $this->countByEmpresa($entityManager, 'App\Entity\Riesgo\Evento', $empresa),
            'plans'     => $this->countByEmpresa($entityManager, 'App\Entity\Riesgo\Plan', $empresa),
            'evaluations'     => $this->countByEmpresa($entityManager, 'App\Entity\Riesgo\Evaluacion', $empresa),
        ];

        return $resumen;
    }

    private function countByEmpresa($em, string $entityClass, Empresa $empresa): int
    {
        return (int) $em->createQuery("
            SELECT COUNT(e.id) FROM $entityClass e
            WHERE e.empresa = :empresa
        ")
        ->setParameter('empresa', $empresa)
        ->getSingleScalarResult();
    }
}
