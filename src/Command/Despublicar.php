<?php



// src/Command/ExampleCommand.php

namespace App\Command;



use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;



// 1. Import the ORM EntityManager Interface

use Doctrine\ORM\EntityManagerInterface;



class Despublicar extends Command

{

    // the name of the command (the part after "bin/console")

    protected static $defaultName = 'app:despublicar';

    

    // 2. Expose the EntityManager in the class level

    private $entityManager;



    public function __construct(EntityManagerInterface $entityManager)

    {

        // 3. Update the value of the private entityManager variable through injection

        $this->entityManager = $entityManager;



        parent::__construct();

    }

    

    protected function configure()

    {

        // ...

    }

}