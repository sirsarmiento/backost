<?php

namespace App\Entity;

use App\Entity\Empresa;
use App\Repository\ModuloRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ModuloRepository::class)
 * @ORM\HasLifecycleCallbacks() 

 */
class Modulo
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
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icono;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Modulo::class)
     */
    private $padre;

    /**
     * @ORM\OneToMany(targetEntity=Modulo::class, mappedBy="padre")
     */
    private $hijo;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $updateBy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $orden;

    /**
      * @ORM\OneToMany(targetEntity="ModuloRol" , mappedBy="modulo")
     * */
    protected $modulorol;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tipoComponente;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="idmodulo")
     */
    private $idempresa;

    public function __construct()
    {
        $this->hijo = new ArrayCollection();
        $this->modulorol = new ArrayCollection();
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getIcono(): ?string
    {
        return $this->icono;
    }

    public function setIcono(?string $icono): self
    {
        $this->icono = $icono;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(?\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function setUpdateBy(string $updateBy): self
    {
        $this->updateBy = $updateBy;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPadre(): ?self
    {
        return $this->padre;
    }

    public function setPadre(?self $padre): self
    {
        $this->padre = $padre;

        return $this;
    }

    /**
     * @return Collection|Modulo[]
     */
    public function getHijo(): Collection
    {
        return $this->hijo;
    }

    public function addHijo(Modulo $hijo): self
    {
        if (!$this->hijo->contains($hijo)) {
            $this->hijo[] = $hijo;
            $hijo->setPadre($this);
        }

        return $this;
    }

    public function removeHijo(Modulo $hijo): self
    {
        if ($this->hijo->removeElement($hijo)) {
            // set the owning side to null (unless already changed)
            if ($hijo->getPadre() === $this) {
                $hijo->setPadre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ModuloRol[]
     */
    public function getModulorol(): Collection
    {
        return $this->modulorol;
    }

    public function addModulorol(ModuloRol $modulorol): self
    {
        if (!$this->modulorol->contains($modulorol)) {
            $this->modulorol[] = $modulorol;
            $modulorol->setModulo($this);
        }

        return $this;
    }

    public function removeModulorol(ModuloRol $modulorol): self
    {
        if ($this->modulorol->removeElement($modulorol)) {
            // set the owning side to null (unless already changed)
            if ($modulorol->getModulo() === $this) {
                $modulorol->setModulo(null);
            }
        }

        return $this;
    }

    public function getTipoComponente(): ?string
    {
        return $this->tipoComponente;
    }

    public function setTipoComponente(?string $tipoComponente): self
    {
        $this->tipoComponente = $tipoComponente;

        return $this;
    }

    public function getIdempresa(): ?Empresa
    {
        return $this->idempresa;
    }

    public function setIdempresa(?Empresa $idempresa): self
    {
        $this->idempresa = $idempresa;

        return $this;
    }

}
