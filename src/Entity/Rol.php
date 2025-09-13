<?php

namespace App\Entity;

use App\Entity\Empresa;
use App\Repository\RolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=RolRepository::class)
  * @UniqueEntity(
    *     fields={"descripcion"},
    *     message="El nombre del Rol ya existe"
 * )
 * @ORM\HasLifecycleCallbacks() 
 */
class Rol
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
    private $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class)
     */
    private $idStatus;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="roles1")
     */
    private $yes;



    /**
      * @ORM\OneToMany(targetEntity="ModuloRol" , mappedBy="rol")
     * */
    protected $modulorol;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="idrol")
     */
    private $idempresa;

    public function __construct()
    {
        $this->yes = new ArrayCollection();
        $this->modulorol = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdStatus(): ?Status
    {
        return $this->idStatus;
    }

    public function setIdStatus(?Status $idStatus): self
    {
        $this->idStatus = $idStatus;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getYes(): Collection
    {
        return $this->yes;
    }

    public function addYe(User $ye): self
    {
        if (!$this->yes->contains($ye)) {
            $this->yes[] = $ye;
            $ye->addRoles1($this);
        }

        return $this;
    }

    public function removeYe(User $ye): self
    {
        if ($this->yes->removeElement($ye)) {
            $ye->removeRoles1($this);
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
            $modulorol->setRol($this);
        }

        return $this;
    }

    public function removeModulorol(ModuloRol $modulorol): self
    {
        if ($this->modulorol->removeElement($modulorol)) {
            // set the owning side to null (unless already changed)
            if ($modulorol->getRol() === $this) {
                $modulorol->setRol(null);
            }
        }

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
