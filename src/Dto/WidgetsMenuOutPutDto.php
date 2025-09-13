<?php

namespace App\Dto;

use App\Repository\TiporedesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use OpenApi\Annotations as OA;

class WidgetsMenuOutPutDto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $idModulo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $nombreModulo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $tipoComponente;


    public function getIdModulo(): ?int
    {
        return $this->idModulo;
    }

    public function getNombreModulo(): ?string
    {
        return $this->nombreModulo;
    }
    public function getTipoComponente(): ?string
    {
        return $this->tipoComponente;
    }
  
}
