<?php

namespace App\Entity\Costo;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CompraLinea
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity=Compra::class, inversedBy="lineas") */
    private $compra;

    /** @ORM\ManyToOne(targetEntity=Activo::class) */
    private $activo;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $cantidad;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $valorUnitario;

    public function getId(): ?int { return $this->id; }
    public function getCompra(): ?Compra { return $this->compra; }
    public function setCompra(?Compra $compra): self { $this->compra = $compra; return $this; }
    public function getActivo(): ?Activo { return $this->activo; }
    public function setActivo(?Activo $activo): self { $this->activo = $activo; return $this; }
    public function getCantidad() { return $this->cantidad; }
    public function setCantidad($cantidad): self { $this->cantidad = $cantidad; return $this; }
    public function getValorUnitario() { return $this->valorUnitario; }
    public function setValorUnitario($valorUnitario): self { $this->valorUnitario = $valorUnitario; return $this; }
}
