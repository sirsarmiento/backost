<?php

namespace App\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class MenuOutPutDto 
{
    /**
     * @ORM\Column(type="integer")
     */
    public $id;

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


    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }


}
