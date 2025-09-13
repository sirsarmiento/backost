<?php

namespace App\Entity;
use App\Repository\EstructuraOrganizativaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EstructuraOrganizativaRepository::class)
 */
class EstructuraOrganizativa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $padre_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $estructura_organizativa;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $jerarquia;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $cod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPadreId(): ?int
    {
        return $this->padre_id;
    }

    public function setPadreId(?int $padre_id): self
    {
        $this->padre_id = $padre_id;

        return $this;
    }

    public function getEstructuraOrganizativa(): ?string
    {
        return $this->estructura_organizativa;
    }

    public function setEstructuraOrganizativa(string $estructura_organizativa): self
    {
        $this->estructura_organizativa = $estructura_organizativa;

        return $this;
    }

    public function getJerarquia(): ?string
    {
        return $this->jerarquia;
    }

    public function setJerarquia(string $jerarquia): self
    {
        $this->jerarquia = $jerarquia;

        return $this;
    }

    public function getCod(): ?string
    {
        return $this->cod;
    }

    public function setCod(?string $cod): self
    {
        $this->cod = $cod;

        return $this;
    }

}
