<?php

namespace App\Entity;

use App\Repository\CorreoSubjectRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CorreoSubjectRepository::class)
 */
class CorreoSubject
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
    private $nombre_subject;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreSubject(): ?string
    {
        return $this->nombre_subject;
    }

    public function setNombreSubject(string $nombre_subject): self
    {
        $this->nombre_subject = $nombre_subject;

        return $this;
    }
}
