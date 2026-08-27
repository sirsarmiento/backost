<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductoRepository::class)
 */
class Producto
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
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $medida;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $clasificacion;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="productos")
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

    /**
     * @ORM\OneToMany(targetEntity=Costo::class, mappedBy="producto")
     */
    private $costos;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="productos")
     */
    private $perfil;

    /**
     * @ORM\OneToMany(targetEntity=Codigo::class, mappedBy="producto")
     */
    private $codigos;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $tasaFallo;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $tiempoSetup;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $postProcesado;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $margenGanancia;

    /**
     * @ORM\OneToMany(targetEntity=Piezas::class, mappedBy="producto")
     */
    private $piezas;

    /**
     * @ORM\OneToMany(targetEntity=PiezasProducto::class, mappedBy="producto")
     */
    private $PiezasProducto;

    /**
     * @ORM\OneToMany(targetEntity=Presupuesto::class, mappedBy="producto")
     */
    private $presupuestos;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system'; // Default creator, can be changed later
        $this->updateAt = null; // Initially no updates
        $this->updateBy = null; // Initially no updates
        $this->costos = new ArrayCollection();
        $this->codigos = new ArrayCollection();
        $this->piezas = new ArrayCollection();
        $this->PiezasProducto = new ArrayCollection();
        $this->presupuestos = new ArrayCollection();
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

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getMedida(): ?string
    {
        return $this->medida;
    }

    public function setMedida(string $medida): self
    {
        $this->medida = $medida;

        return $this;
    }

    public function getClasificacion(): ?string
    {
        return $this->clasificacion;
    }

    public function setClasificacion(string $clasificacion): self
    {
        $this->clasificacion = $clasificacion;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

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

    /**
     * @return Collection|Costo[]
     */
    public function getCostos(): Collection
    {
        return $this->costos;
    }

    public function addCosto(Costo $costo): self
    {
        if (!$this->costos->contains($costo)) {
            $this->costos[] = $costo;
            $costo->setProducto($this);
        }

        return $this;
    }

    public function removeCosto(Costo $costo): self
    {
        if ($this->costos->removeElement($costo)) {
            // set the owning side to null (unless already changed)
            if ($costo->getProducto() === $this) {
                $costo->setProducto(null);
            }
        }

        return $this;
    }

    public function getPerfil(): ?Perfil
    {
        return $this->perfil;
    }

    public function setPerfil(?Perfil $perfil): self
    {
        $this->perfil = $perfil;

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
            $codigo->setProducto($this);
        }

        return $this;
    }

    public function removeCodigo(Codigo $codigo): self
    {
        if ($this->codigos->removeElement($codigo)) {
            // set the owning side to null (unless already changed)
            if ($codigo->getProducto() === $this) {
                $codigo->setProducto(null);
            }
        }

        return $this;
    }

    public function getTasaFallo(): ?string
    {
        return $this->tasaFallo;
    }

    public function setTasaFallo(string $tasaFallo): self
    {
        $this->tasaFallo = $tasaFallo;

        return $this;
    }

    public function getTiempoSetup(): ?string
    {
        return $this->tiempoSetup;
    }

    public function setTiempoSetup(string $tiempoSetup): self
    {
        $this->tiempoSetup = $tiempoSetup;

        return $this;
    }

    public function getPostProcesado(): ?string
    {
        return $this->postProcesado;
    }

    public function setPostProcesado(string $postProcesado): self
    {
        $this->postProcesado = $postProcesado;

        return $this;
    }

    public function getMargenGanancia(): ?string
    {
        return $this->margenGanancia;
    }

    public function setMargenGanancia(string $margenGanancia): self
    {
        $this->margenGanancia = $margenGanancia;

        return $this;
    }

    /**
     * @return Collection|Piezas[]
     */
    public function getPiezas(): Collection
    {
        return $this->piezas;
    }

    public function addPieza(Piezas $pieza): self
    {
        if (!$this->piezas->contains($pieza)) {
            $this->piezas[] = $pieza;
            $pieza->setProducto($this);
        }

        return $this;
    }

    public function removePieza(Piezas $pieza): self
    {
        if ($this->piezas->removeElement($pieza)) {
            // set the owning side to null (unless already changed)
            if ($pieza->getProducto() === $this) {
                $pieza->setProducto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PiezasProducto[]
     */
    public function getPiezasProducto(): Collection
    {
        return $this->PiezasProducto;
    }

    public function addPiezasProducto(PiezasProducto $piezasProducto): self
    {
        if (!$this->PiezasProducto->contains($piezasProducto)) {
            $this->PiezasProducto[] = $piezasProducto;
            $piezasProducto->setProducto($this);
        }

        return $this;
    }

    public function removePiezasProducto(PiezasProducto $piezasProducto): self
    {
        if ($this->PiezasProducto->removeElement($piezasProducto)) {
            // set the owning side to null (unless already changed)
            if ($piezasProducto->getProducto() === $this) {
                $piezasProducto->setProducto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Presupuesto[]
     */
    public function getPresupuestos(): Collection
    {
        return $this->presupuestos;
    }

    public function addPresupuesto(Presupuesto $presupuesto): self
    {
        if (!$this->presupuestos->contains($presupuesto)) {
            $this->presupuestos[] = $presupuesto;
            $presupuesto->setProducto($this);
        }

        return $this;
    }

    public function removePresupuesto(Presupuesto $presupuesto): self
    {
        if ($this->presupuestos->removeElement($presupuesto)) {
            // set the owning side to null (unless already changed)
            if ($presupuesto->getProducto() === $this) {
                $presupuesto->setProducto(null);
            }
        }

        return $this;
    }
}
