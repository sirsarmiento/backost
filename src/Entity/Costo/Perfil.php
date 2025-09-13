<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\PerfilRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PerfilRepository::class)
 */
class Perfil
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
     * @ORM\Column(type="string", length=100)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $sector;

    /**
     * @ORM\Column(type="integer")
     */
    private $empleados;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $rif;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $periodo;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $direccion;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $moneda;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="perfil")
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
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $updateBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\OneToMany(targetEntity=Parametro::class, mappedBy="perfil")
     */
    private $parametros;

    public function __construct()
    {
        $this->parametros = new ArrayCollection();
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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(string $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getEmpleados(): ?int
    {
        return $this->empleados;
    }

    public function setEmpleados(int $empleados): self
    {
        $this->empleados = $empleados;

        return $this;
    }

    public function getRif(): ?string
    {
        return $this->rif;
    }

    public function setRif(string $rif): self
    {
        $this->rif = $rif;

        return $this;
    }

    public function getPeriodo(): ?string
    {
        return $this->periodo;
    }

    public function setPeriodo(string $periodo): self
    {
        $this->periodo = $periodo;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getMoneda(): ?string
    {
        return $this->moneda;
    }

    public function setMoneda(string $moneda): self
    {
        $this->moneda = $moneda;

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

    public function getUpdateBy(): ?string
    {
        return $this->updateBy;
    }

    public function setUpdateBy(?string $updateBy): self
    {
        $this->updateBy = $updateBy;

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

    /**
     * @return Collection|Parametro[]
     */
    public function getParametros(): Collection
    {
        return $this->parametros;
    }

    public function addParametro(Parametro $parametro): self
    {
        if (!$this->parametros->contains($parametro)) {
            $this->parametros[] = $parametro;
            $parametro->setPerfil($this);
        }

        return $this;
    }

    public function removeParametro(Parametro $parametro): self
    {
        if ($this->parametros->removeElement($parametro)) {
            // set the owning side to null (unless already changed)
            if ($parametro->getPerfil() === $this) {
                $parametro->setPerfil(null);
            }
        }

        return $this;
    }
}
