<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Entity\Costo\Piezas;
use App\Repository\Costo\PresupuestoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PresupuestoRepository::class)
 */
class Presupuesto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $clasificacion;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $numero;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="presupuestos")
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
     * @ORM\OneToMany(targetEntity=Piezas::class, mappedBy="presupuesto")
     */
    private $piezas;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $costoOperador;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $costoMaquina;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $tasaFalloGlobal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tiempoSetup;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $margenGanancia;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tiempoPostProcesado;

    /**
     * @ORM\Column(type="integer")
     */
    private $cantidadGlobal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $delivery;

    /**
     * @ORM\ManyToOne(targetEntity=Cliente::class, inversedBy="presupuestos")
     */
    private $cliente;

    /**
     * @ORM\ManyToOne(targetEntity=Producto::class, inversedBy="presupuestos")
     */
    private $producto;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system'; // Default creator, can be changed later
        $this->updateAt = null; // Initially no updates
        $this->updateBy = null; // Initially no updates
        $this->piezas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

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
            $pieza->setPresupuesto($this);
        }

        return $this;
    }

    public function removePieza(Piezas $pieza): self
    {
        if ($this->piezas->removeElement($pieza)) {
            // set the owning side to null (unless already changed)
            if ($pieza->getPresupuesto() === $this) {
                $pieza->setPresupuesto(null);
            }
        }

        return $this;
    }

    public function getCostoOperador(): ?string
    {
        return $this->costoOperador;
    }

    public function setCostoOperador(?string $costoOperador): self
    {
        $this->costoOperador = $costoOperador;

        return $this;
    }

    public function getCostoMaquina(): ?string
    {
        return $this->costoMaquina;
    }

    public function setCostoMaquina(?string $costoMaquina): self
    {
        $this->costoMaquina = $costoMaquina;

        return $this;
    }

    public function getTasaFalloGlobal(): ?string
    {
        return $this->tasaFalloGlobal;
    }

    public function setTasaFalloGlobal(?string $tasaFalloGlobal): self
    {
        $this->tasaFalloGlobal = $tasaFalloGlobal;

        return $this;
    }

    public function getTiempoSetup(): ?int
    {
        return $this->tiempoSetup;
    }

    public function setTiempoSetup(?int $tiempoSetup): self
    {
        $this->tiempoSetup = $tiempoSetup;

        return $this;
    }

    public function getMargenGanancia(): ?string
    {
        return $this->margenGanancia;
    }

    public function setMargenGanancia(?string $margenGanancia): self
    {
        $this->margenGanancia = $margenGanancia;

        return $this;
    }

    public function getTiempoPostProcesado(): ?int
    {
        return $this->tiempoPostProcesado;
    }

    public function setTiempoPostProcesado(?int $tiempoPostProcesado): self
    {
        $this->tiempoPostProcesado = $tiempoPostProcesado;

        return $this;
    }

    public function getCantidadGlobal(): ?string
    {
        return $this->cantidadGlobal;
    }

    public function setCantidadGlobal(string $cantidadGlobal): self
    {
        $this->cantidadGlobal = $cantidadGlobal;

        return $this;
    }

    public function getDelivery(): ?string
    {
        return $this->delivery;
    }

    public function setDelivery(string $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): self
    {
        $this->cliente = $cliente;

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
}
