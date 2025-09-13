<?php

namespace App\Dto;

use App\Repository\RedesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RedesRepository::class)
 */
class RedesOutPutDto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity=Tiporedes::class, inversedBy="idredes")
     * @ORM\JoinColumn(nullable=false)
     */
    public $tiporedes_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="iduser_redes")
     * @ORM\JoinColumn(nullable=false)
     */
    public $id_user_id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $red;

        /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $createAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $createBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $updateAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $updateBy;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTiporedesId(): ?Tiporedes
    {
        return $this->tiporedes_id;
    }

    public function setTiporedesId(?Tiporedes $tiporedes_id): self
    {
        $this->tiporedes_id = $tiporedes_id;

        return $this;
    }

    public function getIdUserId(): ?User
    {
        return $this->id_user_id;
    }

    public function setIdUserId(?User $id_user_id): self
    {
        $this->id_user_id = $id_user_id;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(?\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getCreateBy(): ?string
    {
        return $this->createBy;
    }

    public function setCreateBy(?string $createBy): self
    {
        $this->createBy = $createBy;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getUpdateBy(): ?string
    {
        return $this->updateBy;
    }

    public function setUpdateBy(?string $updateBy): self
    {
        $this->updateBy = $updateBy;

        return $this;
    }

    public function getRed(): ?string
    {
        return $this->red;
    }

    public function setRed(string $red): self
    {
        $this->red = $red;

        return $this;
    }

}
