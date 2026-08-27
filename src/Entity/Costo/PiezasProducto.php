<?php

namespace App\Entity\Costo;

use App\Repository\Costo\PiezasProductoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PiezasProductoRepository::class)
 */
class PiezasProducto
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
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $precioMaterial;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $tipo;

    /**
     * @ORM\Column(type="integer")
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity=Producto::class, inversedBy="PiezasProducto")
     */
    private $producto;

    /**
     * @ORM\ManyToOne(targetEntity=Activo::class)
     */
    private $activo;

    /**
     * @ORM\ManyToOne(targetEntity=Activo::class)
     */
    private $maquina;

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

    public function getPrecioMaterial(): ?string
    {
        return $this->precioMaterial;
    }

    public function setPrecioMaterial(string $precioMaterial): self
    {
        $this->precioMaterial = $precioMaterial;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getProducto(): ?Producto
    {
        return $this->producto;
    }

    public function setProducto(?Producto $producto): self
    {
        $this->producto = $producto;

        return $this;
    }

    public function getActivo(): ?Activo
    {
        return $this->activo;
    }

    public function setActivo(?Activo $activo): self
    {
        $this->activo = $activo;

        return $this;
    }

    public function getMaquina(): ?Activo
    {
        return $this->maquina;
    }

    public function setMaquina(?Activo $maquina): self
    {
        $this->maquina = $maquina;

        return $this;
    }
}
