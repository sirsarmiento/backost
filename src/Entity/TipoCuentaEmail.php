<?php

namespace App\Entity;

use App\Entity\Empresa;
use App\Repository\TipoCuentaEmailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TipoCuentaEmailRepository::class)
 */
class TipoCuentaEmail
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
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $smtp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imap;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pop3;

     /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="idtipocuentaemail")
     */
    private $idempresa;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getSmtp(): ?string
    {
        return $this->smtp;
    }

    public function setSmtp(?string $smtp): self
    {
        $this->smtp = $smtp;

        return $this;
    }

    public function getImap(): ?string
    {
        return $this->imap;
    }

    public function setImap(?string $imap): self
    {
        $this->imap = $imap;

        return $this;
    }

    public function getPop3(): ?string
    {
        return $this->pop3;
    }

    public function setPop3(?string $pop3): self
    {
        $this->pop3 = $pop3;

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
