<?php

namespace App\Dto;



use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class GerenciaOutPutDto
{
    /**
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    public $nombre;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $createAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $createBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $updateAt;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $updateBy;

    /**
    * @OA\Property(
    *      type="array",
    *      @OA\Items(
    *          type="array",
    *          @OA\Items()
    *      ),
    *      description="status"
    * )     */

    public $IdStatus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

 
    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

 
    public function getCreateBy(): ?string
    {
        return $this->createBy;
    }

 
    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

 
    public function getUpdateBy(): ?string
    {
        return $this->updateBy;
    }

 
    public function getIdStatus(): ?StatusOutPutDto
    {
        return $this->IdStatus;
    }

 
}
