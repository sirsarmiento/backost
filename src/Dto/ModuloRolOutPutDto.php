<?php
namespace App\Dto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


class ModuloRolOutPutDto
{
    /**
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    public $idModulo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $nombreModulo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $tipoComponente;

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
    * @OA\Property(
    *      type="array",
    *      @OA\Items(
    *          type="array",
    *          @OA\Items()
    *      ),
    *      description="autorizaciones"
    * )     */
    public $autorizaciones;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $rol;





    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreModulo(): ?string
    {
        return $this->nombreModulo;
    }

    public function getIdModulo(): ?int
    {
        return $this->idModulo;
    }


    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function getRol(): ?string
    {
        return $this->rol;
    }


    

    public function getTipoComponente(): ?string
    {
        return $this->tipoComponente;
    }




}
