<?php

namespace App\Entity\Costo;

use App\Repository\Costo\PiezasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PiezasRepository::class)
 */
class Piezas
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombre;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $gramos;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $metros;

    /**
     * @ORM\Column(type="integer")
     */
    private $horas;

    /**
     * @ORM\Column(type="integer")
     */
    private $minutos;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $updateBy;

    /**
     * @ORM\ManyToOne(targetEntity=Presupuesto::class, inversedBy="piezas")
     */
    private $presupuesto;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $precioMaterial;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getGramos(): ?string
    {
        return $this->gramos;
    }

    public function setGramos(string $gramos): self
    {
        $this->gramos = $gramos;

        return $this;
    }

    public function getMetros(): ?string
    {
        return $this->metros;
    }

    public function setMetros(string $metros): self
    {
        $this->metros = $metros;

        return $this;
    }

    public function getHoras(): ?int
    {
        return $this->horas;
    }

    public function setHoras(int $horas): self
    {
        $this->horas = $horas;

        return $this;
    }

    public function getMinutos(): ?int
    {
        return $this->minutos;
    }

    public function setMinutos(int $minutos): self
    {
        $this->minutos = $minutos;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getUpdateBy(): ?string
    {
        return $this->updateBy;
    }

    public function setUpdateBy(?string $updateBy): self
    {
        $this->updateBy = $updateBy;

        return $this;
    }

    public function getPresupuesto(): ?Presupuesto
    {
        return $this->presupuesto;
    }

    public function setPresupuesto(?Presupuesto $presupuesto): self
    {
        $this->presupuesto = $presupuesto;

        return $this;
    }

    public function getPrecioMaterial(): ?string
    {
        return $this->precioMaterial;
    }

    public function setPrecioMaterial(string $precioMaterial): self
    {
        $this->precioMaterial = $precioMaterial;

        return $this;
    }
}
