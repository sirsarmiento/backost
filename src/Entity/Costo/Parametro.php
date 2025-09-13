<?php

namespace App\Entity\Costo;

use App\Repository\Costo\ParametroRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParametroRepository::class)
 */
class Parametro
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $unidad;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="integer")
     */
    private $prodMaxHoras;

    /**
     * @ORM\Column(type="integer")
     */
    private $horasMax;

    /**
     * @ORM\Column(type="integer")
     */
    private $horasUso;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="parametros")
     */
    private $perfil;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUnidad(): ?string
    {
        return $this->unidad;
    }

    public function setUnidad(string $unidad): self
    {
        $this->unidad = $unidad;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getProdMaxHoras(): ?int
    {
        return $this->prodMaxHoras;
    }

    public function setProdMaxHoras(int $prodMaxHoras): self
    {
        $this->prodMaxHoras = $prodMaxHoras;

        return $this;
    }

    public function getHorasMax(): ?int
    {
        return $this->horasMax;
    }

    public function setHorasMax(int $horasMax): self
    {
        $this->horasMax = $horasMax;

        return $this;
    }

    public function getHorasUso(): ?int
    {
        return $this->horasUso;
    }

    public function setHorasUso(int $horasUso): self
    {
        $this->horasUso = $horasUso;

        return $this;
    }

    public function getPerfil(): ?Perfil
    {
        return $this->perfil;
    }

    public function setPerfil(?Perfil $perfil): self
    {
        $this->perfil = $perfil;

        return $this;
    }
}
