<?php

namespace App\Entity\Costo;

use App\Repository\Costo\SubFamiliaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubFamiliaRepository::class)
 */
class SubFamilia
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
     * @ORM\ManyToOne(targetEntity=Familia::class, inversedBy="subFamilias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $familia;

    /**
     * @ORM\OneToMany(targetEntity=Codigo::class, mappedBy="subfamilia")
     */
    private $codigos;

    public function __construct()
    {
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

    public function getFamilia(): ?Familia
    {
        return $this->familia;
    }

    public function setFamilia(?Familia $familia): self
    {
        $this->familia = $familia;

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
            $codigo->setSubfamilia($this);
        }

        return $this;
    }

    public function removeCodigo(Codigo $codigo): self
    {
        if ($this->codigos->removeElement($codigo)) {
            // set the owning side to null (unless already changed)
            if ($codigo->getSubfamilia() === $this) {
                $codigo->setSubfamilia(null);
            }
        }

        return $this;
    }
}
