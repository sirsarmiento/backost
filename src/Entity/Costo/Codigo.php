<?php

namespace App\Entity\Costo;

use App\Repository\Costo\CodigoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CodigoRepository::class)
 */
class Codigo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categoria;

    /**
     * @ORM\ManyToOne(targetEntity=Producto::class, inversedBy="codigos")
     */
    private $producto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tecnologia;

    /**
     * @ORM\ManyToOne(targetEntity=Familia::class, inversedBy="codigos")
     */
    private $familia;

    /**
     * @ORM\ManyToOne(targetEntity=SubFamilia::class, inversedBy="codigos")
     */
    private $subfamilia;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $material;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $codigo;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $catalogo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createAt;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $createBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $updateBy;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system'; // Default creator, can be changed later
        $this->updateAt = null; // Initially no updates
        $this->updateBy = null; // Initially no updates
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): self
    {
        $this->categoria = $categoria;

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

    public function getTecnologia(): ?string
    {
        return $this->tecnologia;
    }

    public function setTecnologia(string $tecnologia): self
    {
        $this->tecnologia = $tecnologia;

        return $this;
    }

    public function getFamilia(): ?Familia
    {
        return $this->familia;
    }

    public function setFamilia(?Familia $familia): self
    {
        $this->familia = $familia;

        return $this;
    }

    public function getSubfamilia(): ?SubFamilia
    {
        return $this->subfamilia;
    }

    public function setSubfamilia(?SubFamilia $subfamilia): self
    {
        $this->subfamilia = $subfamilia;

        return $this;
    }

    public function getMaterial(): ?string
    {
        return $this->material;
    }

    public function setMaterial(string $material): self
    {
        $this->material = $material;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getCatalogo(): ?string
    {
        return $this->catalogo;
    }

    public function setCatalogo(string $catalogo): self
    {
        $this->catalogo = $catalogo;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getCreateBy(): ?string
    {
        return $this->createBy;
    }

    public function setCreateBy(string $createBy): self
    {
        $this->createBy = $createBy;

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
}
