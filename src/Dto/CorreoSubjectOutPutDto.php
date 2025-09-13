<?php

namespace App\Dto;

use App\Repository\CorreoSubjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * @ORM\Entity(repositoryClass=CorreoSubjectRepository::class)
 */
class CorreoSubjectOutPutDto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $nombresubject;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreSubject(): ?string
    {
        return $this->nombresubject;
    }
}
