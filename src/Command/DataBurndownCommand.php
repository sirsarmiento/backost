<?php

// src/Command/ExampleCommand.php
namespace App\Command;
use App\Entity\Proyecto\ItemsHorasTrabajadas;
Use App\Entity\User;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// 1. Import the ORM EntityManager Interface
use Doctrine\ORM\EntityManagerInterface;

class DataBurndownCommand  extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:DataBurndown';
    private $security;
    
    // 2. Expose the EntityManager in the class level
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager,Security $security,ValidatorInterface $validator)
    {
        // 3. Update the value of the private entityManager variable through injection
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->validator = $validator;
        parent::__construct();
    }
    
    protected function configure()
    {
        // ...
    }

    // 4. Use the entity manager in the command code ...
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $fechaes = date("Y-m-d");
        $fech_pasada = strtotime($fechaes);
        $fech_pasada = $fech_pasada - 1;
        $fechanterior = date("Y-m-d",$fech_pasada); 
        //$fechanterior = date("Y-m-d",strtotime($fechaes."- 1 week")); 
        
        $sql = "SELECT p.id as idproyecto, p.nombre as nombreproyecto, p.fechainicio as fechainicioproyecto, p.fechafin as fechafinproyecto, p.idstatuscalendarioproyecto_id, s.fechainicio as fechainiciospring, s.fechafin as fechafinspring,
        s.nombre as nombrespring, i.id as iditemhorastrabajadas, i.pesotrabajado, i.fecha, i.idspring, i.esperadorestante
        FROM `proyecto` p INNER JOIN spring s on p.id = s.idproyecto_id INNER JOIN items_horas_trabajadas i on i.idspring = s.id 
        where p.idstatuscalendarioproyecto_id =1 and DATE(fecha) between "." '".$fechanterior."' AND '".$fechanterior."'  order by iditemhorastrabajadas ASC";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $entityItemshorastrabajadas=$stmt->fetchAll();

        $fech_fincompara1 = strtotime($entityItemshorastrabajadas[0]['fechafinspring']);
        $fech_inicocompara = date('Y-m-d', $fech_fincompara1);
        $fech_fincompara1 = strtotime($fech_inicocompara);
        $fecha1 = $fechaes;
        $fecha = strtotime($fecha1);
        if (($fecha > $fech_fincompara1)){
        }else{
        if ($entityItemshorastrabajadas) {
            foreach($entityItemshorastrabajadas as $reganterior){               
                /* $iditemshorastrabajadas = $telef->getId();
                $pesoremanente= $telef->getPesotrabajado(); */
                $idit = $reganterior["idproyecto"];

                $sql3 = " SELECT idspring, pesotrabajado, fecha  FROM items_horas_trabajadas where idspring=".$reganterior["idspring"]." and DATE(fecha) between "." '".$fechaes."' AND '".$fechaes."'  order by id ASC"; 
                $conn1 = $this->entityManager->getConnection();
                $stmt = $conn1->prepare($sql3);
                $stmt->execute();
                $buscItemshorastrabajadas=$stmt->fetchAll();
                if ($buscItemshorastrabajadas) {
                    //si lo encontro ya estan
                }else{
                    //si no encontro los registro debe de crearlo con ese valor anterior
                    $entity = new ItemsHorasTrabajadas();
                    $entity->setIdspring($reganterior["idspring"]);
                    $entity->setPesotrabajado($reganterior["pesotrabajado"]);
                    $entity->setEsperadorestante($reganterior["esperadorestante"]);
                    $entity->setRemanente(0);
                    $entity->setTotalRemaningMax(0);
                    $entity->setFecha(new \DateTime('now'));
                    //$currentUser2 =$this->entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
                    $currentUser2 =$this->entityManager->getRepository(User::class)->find(1);
                    $entity->setCreateBy($currentUser2->getUserName());
                    $entity->setCreateAt(new \DateTime('now'));
                    $errors = $this->validator->validate($entity);
                    if($errors->count() > 0){
                        $errorsString = (string) $errors;
                        return new JsonResponse(['msg'=>$errorsString],500);
                    }else{
                        $this->entityManager->persist($entity);
                        $this->entityManager->flush();
                    }  
                }
            }
        }else{
            //No hay nada en esa fecha en la tabla esta vacia
        }
       }
        $io->success('Registro creado con exito.'.$fechaes);
        return Command::SUCCESS;
    }
}