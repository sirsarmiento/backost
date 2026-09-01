<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\VentaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VentaRepository::class)
 */
class Venta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string", length=50) */
    private $numero;

    /** @ORM\Column(type="date") */
    private $fecha;

    /** @ORM\Column(type="string", length=1000, nullable=true) */
    private $descripcion;

    /** @ORM\ManyToOne(targetEntity=Presupuesto::class) */
    private $presupuesto;

    /** @ORM\ManyToOne(targetEntity=Cliente::class) */
    private $cliente;

    /** @ORM\ManyToOne(targetEntity=Producto::class) */
    private $producto;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $cantidad;

    /** @ORM\Column(type="decimal", precision=12, scale=2) */
    private $total = 0;

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
        $this->fecha = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getNumero(): ?string { return $this->numero; }
    public function setNumero(string $numero): self { $this->numero = $numero; return $this; }
    public function getFecha(): ?\DateTimeInterface { return $this->fecha; }
    public function setFecha(\DateTimeInterface $fecha): self { $this->fecha = $fecha; return $this; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function setDescripcion(?string $descripcion): self { $this->descripcion = $descripcion; return $this; }
    public function getPresupuesto(): ?Presupuesto { return $this->presupuesto; }
    public function setPresupuesto(?Presupuesto $presupuesto): self { $this->presupuesto = $presupuesto; return $this; }
    public function getCliente(): ?Cliente { return $this->cliente; }
    public function setCliente(?Cliente $cliente): self { $this->cliente = $cliente; return $this; }
    public function getProducto(): ?Producto { return $this->producto; }
    public function setProducto(?Producto $producto): self { $this->producto = $producto; return $this; }
    public function getCantidad() { return $this->cantidad; }
    public function setCantidad($cantidad): self { $this->cantidad = $cantidad; return $this; }
    public function getTotal() { return $this->total; }
    public function setTotal($total): self { $this->total = $total; return $this; }
    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }
    public function getCreateAt(): ?\DateTimeInterface { return $this->createAt; }
    public function setCreateAt(\DateTimeInterface $createAt): self { $this->createAt = $createAt; return $this; }
    public function getCreateBy(): ?string { return $this->createBy; }
    public function setCreateBy(string $createBy): self { $this->createBy = $createBy; return $this; }
}
