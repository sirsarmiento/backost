<?php

namespace App\Dto;

use App\Repository\TiporedesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use OpenApi\Annotations as OA;

class AutorizacionesOutPutDto
{
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
  
}
