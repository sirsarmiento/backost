<?php

namespace App\Dto;
use App\Repository\EstructuraOrganizativaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EstructuraOrganizativaRepository::class)
 */
class EstructuraOrganizativaOutPutDto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public $padreid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $estructuraorganizativa;

    /**
     * @ORM\Column(type="string", length=10)
     */
    public $jerarquia;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    public $cod;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getPadreId(): ?int
    {
        return $this->padreid;
    }
    public function getEstructuraOrganizativa(): ?string
    {
        return $this->estructuraorganizativa;
    }
    public function getJerarquia(): ?string
    {
        return $this->jerarquia;
    }

    public function getCod(): ?string
    {
        return $this->cod;
    }
}
