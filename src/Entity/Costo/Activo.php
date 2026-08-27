<?php

namespace App\Entity\Costo;

use App\Entity\Empresa;
use App\Repository\Costo\ActivoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $categoria;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subCategoria;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $consumoMaquina;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $tarifa;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $costoMantenimiento;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $tipo;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $cantidad;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $unidadMedida;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $presentacion;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ubicacion;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $valorUnitario;

    /**
     * @ORM\OneToMany(targetEntity=Piezas::class, mappedBy="activo")
     */
    private $piezas;

    /**
     * @ORM\OneToMany(targetEntity=Piezas::class, mappedBy="maquina")
     */
    private $piezasMaquina;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->createBy = 'system'; // Default creator, can be changed later
        $this->updateAt = null; // Initially no updates
        $this->updateBy = null; // Initially no updates
        $this->piezas = new ArrayCollection();
        $this->piezasMaquina = new ArrayCollection(); // ¡NUEVO!
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

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(?string $categoria): self
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getSubCategoria(): ?string
    {
        return $this->subCategoria;
    }

    public function setSubCategoria(?string $subCategoria): self
    {
        $this->subCategoria = $subCategoria;

        return $this;
    }

    public function getConsumoMaquina(): ?string
    {
        return $this->consumoMaquina;
    }

    public function setConsumoMaquina(string $consumoMaquina): self
    {
        $this->consumoMaquina = $consumoMaquina;

        return $this;
    }

    public function getTarifa(): ?string
    {
        return $this->tarifa;
    }

    public function setTarifa(string $tarifa): self
    {
        $this->tarifa = $tarifa;

        return $this;
    }

    public function getCostoMantenimiento(): ?string
    {
        return $this->costoMantenimiento;
    }

    public function setCostoMantenimiento(string $costoMantenimiento): self
    {
        $this->costoMantenimiento = $costoMantenimiento;

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

    public function getCantidad(): ?string
    {
        return $this->cantidad;
    }

    public function setCantidad(string $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getUnidadMedida(): ?string
    {
        return $this->unidadMedida;
    }

    public function setUnidadMedida(string $unidadMedida): self
    {
        $this->unidadMedida = $unidadMedida;

        return $this;
    }

    public function getPresentacion(): ?string
    {
        return $this->presentacion;
    }

    public function setPresentacion(?string $presentacion): self
    {
        $this->presentacion = $presentacion;

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

    public function getUbicacion(): ?string
    {
        return $this->ubicacion;
    }

    public function setUbicacion(?string $ubicacion): self
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    public function getValorUnitario(): ?string
    {
        return $this->valorUnitario;
    }

    public function setValorUnitario(string $valorUnitario): self
    {
        $this->valorUnitario = $valorUnitario;

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
            $pieza->setActivo($this);
        }

        return $this;
    }

    public function removePieza(Piezas $pieza): self
    {
        if ($this->piezas->removeElement($pieza)) {
            // set the owning side to null (unless already changed)
            if ($pieza->getActivo() === $this) {
                $pieza->setActivo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Piezas[]
     */
    public function getPiezasMaquina(): Collection
    {
        return $this->piezasMaquina;
    }

    public function addPiezasMaquina(Piezas $pieza): self
    {
        if (!$this->piezasMaquina->contains($pieza)) {
            $this->piezasMaquina[] = $pieza;
            $pieza->setMaquina($this);
        }

        return $this;
    }

    public function removePiezasMaquina(Piezas $pieza): self
    {
        if ($this->piezasMaquina->removeElement($pieza)) {
            if ($pieza->getMaquina() === $this) {
                $pieza->setMaquina(null);
            }
        }

        return $this;
    }
}
