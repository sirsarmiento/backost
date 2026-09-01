<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\CompraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompraRepository::class)
 */
class Compra
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

    /** @ORM\ManyToOne(targetEntity=Proveedor::class, inversedBy="compras") */
    private $proveedor;

    /** @ORM\Column(type="string", length=1000, nullable=true) */
    private $observacion;

    /** @ORM\Column(type="decimal", precision=12, scale=2) */
    private $total = 0;

    /** @ORM\ManyToOne(targetEntity=Empresa::class) */
    private $empresa;

    /** @ORM\Column(type="datetime") */
    private $createAt;

    /** @ORM\Column(type="string", length=50) */
    private $createBy;

    /**
     * @ORM\OneToMany(targetEntity=CompraLinea::class, mappedBy="compra", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $lineas;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system';
        $this->fecha = new \DateTime();
        $this->lineas = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNumero(): ?string { return $this->numero; }
    public function setNumero(string $numero): self { $this->numero = $numero; return $this; }
    public function getFecha(): ?\DateTimeInterface { return $this->fecha; }
    public function setFecha(\DateTimeInterface $fecha): self { $this->fecha = $fecha; return $this; }
    public function getProveedor(): ?Proveedor { return $this->proveedor; }
    public function setProveedor(?Proveedor $proveedor): self { $this->proveedor = $proveedor; return $this; }
    public function getObservacion(): ?string { return $this->observacion; }
    public function setObservacion(?string $observacion): self { $this->observacion = $observacion; return $this; }
    public function getTotal() { return $this->total; }
    public function setTotal($total): self { $this->total = $total; return $this; }
    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }
    public function getCreateAt(): ?\DateTimeInterface { return $this->createAt; }
    public function setCreateAt(\DateTimeInterface $createAt): self { $this->createAt = $createAt; return $this; }
    public function getCreateBy(): ?string { return $this->createBy; }
    public function setCreateBy(string $createBy): self { $this->createBy = $createBy; return $this; }

    /** @return Collection|CompraLinea[] */
    public function getLineas(): Collection { return $this->lineas; }

    public function addLinea(CompraLinea $linea): self
    {
        if (!$this->lineas->contains($linea)) {
            $this->lineas[] = $linea;
            $linea->setCompra($this);
        }
        return $this;
    }
}
