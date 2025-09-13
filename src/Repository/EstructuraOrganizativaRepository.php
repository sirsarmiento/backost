<?php

namespace App\Repository;

use App\Entity\EstructuraOrganizativa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Security\Core\Security;

use App\Dto\EstructuraOrganizativaOutPutDto;

/**
 * @method EstructuraOrganizativa|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstructuraOrganizativa|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstructuraOrganizativa[]    findAll()
 * @method EstructuraOrganizativa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstructuraOrganizativaRepository extends ServiceEntityRepository
{
    private $security;
    public function __construct(ManagerRegistry $registry,Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, EstructuraOrganizativa::class);
    }


    /**
     * Listar Estructura Organizativa.
     */
    public function findList()
    {   
        $entityManagerDefault = $this->getEntityManager();
        $dataEstructuraOrganizativa=[];
        $query = $entityManagerDefault->createQueryBuilder();
        $allAppointmentsQuery = $query->select('estructuraorganizativa.id,estructuraorganizativa.jerarquia,estructuraorganizativa.estructura_organizativa')
        ->from(EstructuraOrganizativa::class,'estructuraorganizativa')
        ->where('estructuraorganizativa.padre_id is null')
        ->addOrderBy('estructuraorganizativa.id', 'ASC')
        ->getQuery();
        $queryult = $query->getQuery();
        $data =  $queryult->execute();
        $cont=0;
        foreach($data as $clave=>$valor){
          $profesionDto =new EstructuraOrganizativaOutPutDto();
          $profesionDto->id=$valor["id"];
          //$profesionDto->padre_id=$valor->getPadreId();
          $profesionDto->estructuraorganizativa=$valor["estructura_organizativa"];
          $profesionDto->jerarquia=$valor["jerarquia"];

          //$profesionDto->estructuraorganizativa=$valor["estructura_organizativa"];
          //$profesionDto->iddireccionalmacen=($valor->getIddireccionalmacen()!=null)?array("id"=>$valor->getIddireccionalmacen()->getId(),"Nombre"=>$valor->getIddireccionalmacen()->getNombre(),"Direccion"=>$valor->getIddireccionalmacen()->getDireccionzona()):[]; 

          $dataEstructuraOrganizativa[]=$profesionDto;
      }
       return $dataEstructuraOrganizativa;
    }


     /**
     * Listar Estructura Organizativa id.
     */
    public function findListid($id)
    {
        $entityManagerDefault = $this->getEntityManager();
        $dataEstructuraOrganizativa=[];
        $query = $entityManagerDefault->createQueryBuilder();
        $allAppointmentsQuery = $query->select('estructuraorganizativa.id,estructuraorganizativa.jerarquia,estructuraorganizativa.estructura_organizativa')
        ->from(EstructuraOrganizativa::class,'estructuraorganizativa')
        ->where("estructuraorganizativa.padre_id ='".$id."'")
        ->addOrderBy('estructuraorganizativa.id', 'ASC')
        ->getQuery();
        $queryult = $query->getQuery();
        $data =  $queryult->execute();
        $cont=0;
        foreach($data as $clave=>$valor){
          $profesionDto =new EstructuraOrganizativaOutPutDto();
          $profesionDto->id=$valor["id"];
          //$profesionDto->padreid=$id;
          //$profesionDto->nivelunidad=$valor["nivel_unidad"];
          $profesionDto->estructuraorganizativa=$valor["estructura_organizativa"];
          $profesionDto->jerarquia=$valor["jerarquia"];
          //$profesionDto->iddireccionalmacen=($valor->getIddireccionalmacen()!=null)?array("id"=>$valor->getIddireccionalmacen()->getId(),"Nombre"=>$valor->getIddireccionalmacen()->getNombre(),"Direccion"=>$valor->getIddireccionalmacen()->getDireccionzona()):[]; 

          $dataEstructuraOrganizativa[]=$profesionDto;
      }
       return $dataEstructuraOrganizativa;
    }
        
    // /**
    //  * @return EstructuraOrganizativa[] Returns an array of EstructuraOrganizativa objects
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
    public function findOneBySomeField($value): ?EstructuraOrganizativa
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
