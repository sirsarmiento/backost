<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\ActivoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActivoRepository::class)
 */
class Activo
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
    private $nombre;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $costoInicial;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $valorResidual;

    /**
     * @ORM\Column(type="integer")
     */
    private $vidaUtil;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaCompra;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="activos")
     */
    private $empresa;

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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCostoInicial(): ?string
    {
        return $this->costoInicial;
    }

    public function setCostoInicial(string $costoInicial): self
    {
        $this->costoInicial = $costoInicial;

        return $this;
    }

    public function getValorResidual(): ?string
    {
        return $this->valorResidual;
    }

    public function setValorResidual(string $valorResidual): self
    {
        $this->valorResidual = $valorResidual;

        return $this;
    }

    public function getVidaUtil(): ?int
    {
        return $this->vidaUtil;
    }

    public function setVidaUtil(int $vidaUtil): self
    {
        $this->vidaUtil = $vidaUtil;

        return $this;
    }

    public function getFechaCompra(): ?\DateTimeInterface
    {
        return $this->fechaCompra;
    }

    public function setFechaCompra(?\DateTimeInterface $fechaCompra): self
    {
        $this->fechaCompra = $fechaCompra;

        return $this;
    }

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

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
