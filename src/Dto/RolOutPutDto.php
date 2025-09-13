<?php
namespace App\Dto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


class RolOutPutDto
{
    /**
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    public $descripcion;

    /**
     * @ORM\Column(type="integer")
     */
    public $id_status_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }


    public function getId_Status_Id(): ?int
    {
        return $this->id_status_id;
    }

    
}
