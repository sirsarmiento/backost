<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\MovimientoInventarioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoInventarioRepository::class)
 */
class MovimientoInventario
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity=Activo::class) */
    private $activo;

    /** @ORM\ManyToOne(targetEntity=Producto::class) */
    private $producto;

    /** @ORM\Column(type="string", length=30) */
    private $tipo;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $cantidad;

    /** @ORM\Column(type="string", length=50, nullable=true) */
    private $referenciaTipo;

    /** @ORM\Column(type="integer", nullable=true) */
    private $referenciaId;

    /** @ORM\Column(type="string", length=1000, nullable=true) */
    private $observacion;

    /** @ORM\ManyToOne(targetEntity=Empresa::class) */
    private $empresa;

    /** @ORM\Column(type="datetime") */
    private $createAt;

    /** @ORM\Column(type="string", length=50) */
    private $createBy;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system';
    }

    public function getId(): ?int { return $this->id; }
    public function getActivo(): ?Activo { return $this->activo; }
    public function setActivo(?Activo $activo): self { $this->activo = $activo; return $this; }
    public function getProducto(): ?Producto { return $this->producto; }
    public function setProducto(?Producto $producto): self { $this->producto = $producto; return $this; }
    public function getTipo(): ?string { return $this->tipo; }
    public function setTipo(string $tipo): self { $this->tipo = $tipo; return $this; }
    public function getCantidad() { return $this->cantidad; }
    public function setCantidad($cantidad): self { $this->cantidad = $cantidad; return $this; }
    public function getReferenciaTipo(): ?string { return $this->referenciaTipo; }
    public function setReferenciaTipo(?string $referenciaTipo): self { $this->referenciaTipo = $referenciaTipo; return $this; }
    public function getReferenciaId(): ?int { return $this->referenciaId; }
    public function setReferenciaId(?int $referenciaId): self { $this->referenciaId = $referenciaId; return $this; }
    public function getObservacion(): ?string { return $this->observacion; }
    public function setObservacion(?string $observacion): self { $this->observacion = $observacion; return $this; }
    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }
    public function getCreateAt(): ?\DateTimeInterface { return $this->createAt; }
    public function setCreateAt(\DateTimeInterface $createAt): self { $this->createAt = $createAt; return $this; }
    public function getCreateBy(): ?string { return $this->createBy; }
    public function setCreateBy(string $createBy): self { $this->createBy = $createBy; return $this; }
}
