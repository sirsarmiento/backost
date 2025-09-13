<?php

// src/Command/ExampleCommand.php
namespace App\Command;
//use App\Entity\Proyecto\Proyecto;
Use App\Entity\User;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// 1. Import the ORM EntityManager Interface
use Doctrine\ORM\EntityManagerInterface;

class EstatusProyectoCommand  extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:EstatusProyecto';
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
        
        $sqlbus1 = " SELECT *  FROM proyecto where idstatuscalendarioproyecto_id=2 order by id ASC"; 
        $conn =  $this->entityManager->getConnection();
        $stmt = $conn->prepare($sqlbus1);
        $stmt->execute();
        $databus1=$stmt->fetchAll();
        $esDiaFeriado=false;
        $ip=0;
        $fechaes = date("Y-m-d");
        $fecha1 = $fechaes;
        $fecha = strtotime($fecha1);
        $dia=date("w", strtotime($fechaes));
        foreach($databus1 as $claveResult2=>$valorResultbusc){
                $fech_fincompara1 = strtotime($valorResultbusc["fechainicio"]);
                $fech_inicocompara = date('Y-m-d', $fech_fincompara1);
                $fech_inicocompara = strtotime($fech_inicocompara);
                if (($fech_inicocompara <= $fecha)){
                    $sql2 = "update proyecto set idstatuscalendarioproyecto_id=1 where id =".$valorResultbusc["id"]." ";
                    $conn2 = $this->entityManager->getConnection();
                    $stmt2 = $conn2->prepare($sql2);
                    $stmt2->execute();  
                }
         }
          $io->success('Registro actualizado con exito.'.$fechaes);
          return Command::SUCCESS;
    }
}