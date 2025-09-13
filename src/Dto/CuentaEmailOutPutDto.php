<?php
namespace App\Dto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


class CuentaEmailOutPutDto
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $password;

   /**
     * @ORM\Column(type="string", length=250)
     */
    public $status;


            /**
    * @OA\Property(
    *      type="array",
    *      @OA\Items(
    *          type="array",
    *          @OA\Items()
    *      ),
    *      description="tipoCuenta"
    * )     */

    public $tipoCuenta;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $nombre;

    public function getId(): ?int
    {
        return $this->id;
    }

  
    public function getEmail(): ?string
    {
        return $this->email;
    }


    public function getPassword(): ?string
    {
        return $this->password;
    }


    public function getStatus(): ?string
    {
        return $this->status;
    }



    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function getTipoCuenta(): ?array
    {
        return $this->tipoCuenta;
    }


}
