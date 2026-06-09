<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\FamiliaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FamiliaRepository::class)
 */
class Familia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $codigo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="familias")
     */
    private $empresa;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
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

    /**
     * @ORM\OneToMany(targetEntity=SubFamilia::class, mappedBy="familia")
     */
    private $subFamilias;

    /**
     * @ORM\OneToMany(targetEntity=Codigo::class, mappedBy="familia")
     */
    private $codigos;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system'; // Default creator, can be changed later
        $this->updateAt = null; // Initially no updates
        $this->updateBy = null; // Initially no updates
        $this->subFamilias = new ArrayCollection();
        $this->codigos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

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

    public function setCreateBy(?string $createBy): self
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

    /**
     * @return Collection|SubFamilia[]
     */
    public function getSubFamilias(): Collection
    {
        return $this->subFamilias;
    }

    public function addSubFamilia(SubFamilia $subFamilia): self
    {
        if (!$this->subFamilias->contains($subFamilia)) {
            $this->subFamilias[] = $subFamilia;
            $subFamilia->setFamilia($this);
        }

        return $this;
    }

    public function removeSubFamilia(SubFamilia $subFamilia): self
    {
        if ($this->subFamilias->removeElement($subFamilia)) {
            // set the owning side to null (unless already changed)
            if ($subFamilia->getFamilia() === $this) {
                $subFamilia->setFamilia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Codigo[]
     */
    public function getCodigos(): Collection
    {
        return $this->codigos;
    }

    public function addCodigo(Codigo $codigo): self
    {
        if (!$this->codigos->contains($codigo)) {
            $this->codigos[] = $codigo;
            $codigo->setFamilia($this);
        }

        return $this;
    }

    public function removeCodigo(Codigo $codigo): self
    {
        if ($this->codigos->removeElement($codigo)) {
            // set the owning side to null (unless already changed)
            if ($codigo->getFamilia() === $this) {
                $codigo->setFamilia(null);
            }
        }

        return $this;
    }
}
