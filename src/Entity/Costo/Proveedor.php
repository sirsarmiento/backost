<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\ProveedorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProveedorRepository::class)
 */
class Proveedor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string", length=255) */
    private $nombre;

    /** @ORM\Column(type="string", length=50, nullable=true) */
    private $rif;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    private $email;

    /** @ORM\Column(type="string", length=50, nullable=true) */
    private $telefono;

    /** @ORM\Column(type="string", length=1000, nullable=true) */
    private $direccion;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $contacto;

    /** @ORM\ManyToOne(targetEntity=Empresa::class) */
    private $empresa;

    /** @ORM\Column(type="datetime") */
    private $createAt;

    /** @ORM\Column(type="string", length=50) */
    private $createBy;

    /** @ORM\OneToMany(targetEntity=Compra::class, mappedBy="proveedor") */
    private $compras;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system';
        $this->compras = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $nombre): self { $this->nombre = $nombre; return $this; }
    public function getRif(): ?string { return $this->rif; }
    public function setRif(?string $rif): self { $this->rif = $rif; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }
    public function getTelefono(): ?string { return $this->telefono; }
    public function setTelefono(?string $telefono): self { $this->telefono = $telefono; return $this; }
    public function getDireccion(): ?string { return $this->direccion; }
    public function setDireccion(?string $direccion): self { $this->direccion = $direccion; return $this; }
    public function getContacto(): ?string { return $this->contacto; }
    public function setContacto(?string $contacto): self { $this->contacto = $contacto; return $this; }
    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }
    public function getCreateAt(): ?\DateTimeInterface { return $this->createAt; }
    public function setCreateAt(\DateTimeInterface $createAt): self { $this->createAt = $createAt; return $this; }
    public function getCreateBy(): ?string { return $this->createBy; }
    public function setCreateBy(string $createBy): self { $this->createBy = $createBy; return $this; }

    /** @return Collection|Compra[] */
    public function getCompras(): Collection { return $this->compras; }
}
