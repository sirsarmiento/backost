<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\DesacopleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DesacopleRepository::class)
 */
class Desacople
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity=Producto::class) */
    private $producto;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $cantidadProducto;

    /** @ORM\Column(type="string", length=1000, nullable=true) */
    private $observacion;

    /** @ORM\ManyToOne(targetEntity=Empresa::class) */
    private $empresa;

    /** @ORM\Column(type="datetime") */
    private $createAt;

    /** @ORM\Column(type="string", length=50) */
    private $createBy;

    /**
     * @ORM\OneToMany(targetEntity=DesacopleLinea::class, mappedBy="desacople", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $lineas;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system';
        $this->lineas = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getProducto(): ?Producto { return $this->producto; }
    public function setProducto(?Producto $producto): self { $this->producto = $producto; return $this; }
    public function getCantidadProducto() { return $this->cantidadProducto; }
    public function setCantidadProducto($cantidadProducto): self { $this->cantidadProducto = $cantidadProducto; return $this; }
    public function getObservacion(): ?string { return $this->observacion; }
    public function setObservacion(?string $observacion): self { $this->observacion = $observacion; return $this; }
    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }
    public function getCreateAt(): ?\DateTimeInterface { return $this->createAt; }
    public function setCreateAt(\DateTimeInterface $createAt): self { $this->createAt = $createAt; return $this; }
    public function getCreateBy(): ?string { return $this->createBy; }
    public function setCreateBy(string $createBy): self { $this->createBy = $createBy; return $this; }

    /** @return Collection|DesacopleLinea[] */
    public function getLineas(): Collection { return $this->lineas; }

    public function addLinea(DesacopleLinea $linea): self
    {
        if (!$this->lineas->contains($linea)) {
            $this->lineas[] = $linea;
            $linea->setDesacople($this);
        }
        return $this;
    }
}
