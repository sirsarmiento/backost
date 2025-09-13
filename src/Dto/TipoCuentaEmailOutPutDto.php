<?php
namespace App\Dto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


class TipoCuentaEmailOutPutDto
{

    /**
     * @ORM\Column(type="id", length=255)
     */
    public $id;


    /**
     * @ORM\Column(type="string", length=255)
     */
    public $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $smtp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $imap;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $pop3;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }


    public function getSmtp(): ?string
    {
        return $this->smtp;
    }

    public function getImap(): ?string
    {
        return $this->imap;
    }


    public function getPop3(): ?string
    {
        return $this->pop3;
    }


}
