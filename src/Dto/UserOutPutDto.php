<?php

namespace App\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class UserOutPutDto 
{
    /**
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    public $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $numeroDocumento;

    /**
     * @ORM\Column(type="string", length=1)
     */
    public $tipoDocumentoIdentidad;

    /**
     * @ORM\Column(type="string", length=50)
     */
    public $primerNombre;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $segundoNombre;

    /**
     * @ORM\Column(type="string", length=50)
     */
    public $primerApellido;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $segundoApellido;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $fechaNacimiento;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    public $email;

   /**
    * @OA\Property(
    *      type="array",
    *      @OA\Items(
    *          type="array",
    *          @OA\Items()
    *      ),
    *      description="status"
    * )     */
   public $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $createAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $updateBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $updateAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $createBy;

   /**
    * @OA\Property(
    *      type="array",
    *      @OA\Items(
    *          type="array",
    *          @OA\Items()
    *      ),
    *      description="tefelefonos"
    * )     */
    public $telefonos;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $Dependencia;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $Gerencia;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $Coordinacion;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $cargo;


    /**
        * @OA\Property(
        *      type="array",
        *      @OA\Items(
        *          type="array",
        *          @OA\Items()
        *      ),
        *      description="roles"
        * )     */
    public $roles;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $foto;

    /**
    * @OA\Property(
    *      type="array",
    *      @OA\Items(
    *          type="array",
    *          @OA\Items()
    *      ),
    *      description="opcionesMenus"
    * )     */
    public $opcionesMenu;


    /**
    * @OA\Property(
    *      type="array",
    *      @OA\Items(
    *          type="array",
    *          @OA\Items()
    *      ),
    *      description="componentes"
    * )     */
    public $componentes;
   
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $pais;

        /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $estado;

        /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $ciudad;

        /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $direccion;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $sexo;

    /**
    * @OA\Property(
    *      type="array",
    *      @OA\Items(
    *          type="array",
    *          @OA\Items()
    *      ),
    *      description="redes"
    * )     */
    public  $redes;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public $idestructura;

    public function __construct()
    {
        $this->telefonos = new ArrayCollection();
    }

    public function setId($id)    {
        return $this->id;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }




    public function getTipoDocumentoIdentidad(): ?string
    {
        return $this->tipoDocumentoIdentidad;
    }


    public function getPrimerNombre(): ?string
    {
        return $this->primerNombre;
    }


    public function getSegundoNombre(): ?string
    {
        return $this->segundoNombre;
    }


    public function getPrimerApellido(): ?string
    {
        return $this->primerApellido;
    }


    public function getSegundoApellido(): ?string
    {
        return $this->segundoApellido;
    }


    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fechaNacimiento;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }



    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }


    public function getUpdateBy(): ?string
    {
        return $this->updateBy;
    }


    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }


    public function getCreateBy(): ?string
    {
        return $this->createBy;
    }


    /**
     * @return Array
     */
    public function getTelefonos()
    {
        return $this->telefonos;
    }


    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;    
        $arr=json_decode($roles,TRUE); 
        return $arr;
    }


    public function getDependencia(): string
    {
        return $this->Dependencia;
    }



    public function propertyNames() {
        return get_object_vars($this);
    }

    public function getNumeroDocumento(): ?string
    {
        return $this->numeroDocumento;
    }


    public function getCargo(): ?CargoOutPutDto
    {
        return $this->cargo;
    }

    
    public function getFoto(): ?string
    {
        return $this->foto;
    }


    /**
     * @see Array
     */
    public function getOpcionesMenu()
    {
        return $this->opcionesMenu;
    }

    /**
     * @see Array
     */
    public function getComponentes()
    {
        return $this->componentes;
    }

    public function getPais(): string
    {
        return $this->pais;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getCiudad(): string
    {
        return $this->ciudad;
    }

    public function getSexo(): string
    {
        return $this->sexo;
    }
    public function getDireccion(): string
    {
        return $this->direccion;
    }

    /**
     * @see Array
     */
    public function getRedes()
    {
        return $this->redes;
    }


    public function getGerencia(): string
    {
        return $this->Gerencia;
    }
    public function getCoordinacion(): string
    {
        return $this->Coordinacion;
    }

    public function getIdestructura(): ?int
    {
        return $this->idestructura;
    }

}
