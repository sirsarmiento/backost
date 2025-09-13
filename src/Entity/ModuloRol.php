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
 * @ORM\Entity
 * @ORM\Table(name="modulo_rol")
 */
class ModuloRol
{

        /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Rol", inversedBy="modulorol")
     * @ORM\JoinColumn(name="rol_id", referencedColumnName="id")
     * */
    protected $rol;

    /**
     * @ORM\ManyToOne(targetEntity="Modulo", inversedBy="modulorol")
     * @ORM\JoinColumn(name="modulo_id", referencedColumnName="id")
     * */
    protected $modulo;


    /**
     * @ORM\Column(type="string", length=2000)
     */
    private $autorizacion;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class)
     */
    private $status;

     /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="idmodulorol")
     */
    private $idempresa;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAutorizacion(): ?string
    {
        $this->autorizacion = stripslashes(trim($this->autorizacion,'"'));
        return $this->autorizacion;
    }

    public function setAutorizacion(string $autorizacion): self
    {
        $this->autorizacion = $autorizacion;

        return $this;
    }

    public function getRol(): ?Rol
    {
        return $this->rol;
    }

    public function setRol(?Rol $rol): self
    {
        $this->rol = $rol;

        return $this;
    }

    public function getModulo(): ?Modulo
    {
        return $this->modulo;
    }

    public function setModulo(?Modulo $modulo): self
    {
        $this->modulo = $modulo;

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
