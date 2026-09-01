<?php

namespace App\Entity\Costo;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DesacopleLinea
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity=Desacople::class, inversedBy="lineas") */
    private $desacople;

    /** @ORM\ManyToOne(targetEntity=Activo::class) */
    private $activo;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $recuperado = 0;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $merma = 0;

    public function getId(): ?int { return $this->id; }
    public function getDesacople(): ?Desacople { return $this->desacople; }
    public function setDesacople(?Desacople $desacople): self { $this->desacople = $desacople; return $this; }
    public function getActivo(): ?Activo { return $this->activo; }
    public function setActivo(?Activo $activo): self { $this->activo = $activo; return $this; }
    public function getRecuperado() { return $this->recuperado; }
    public function setRecuperado($recuperado): self { $this->recuperado = $recuperado; return $this; }
    public function getMerma() { return $this->merma; }
    public function setMerma($merma): self { $this->merma = $merma; return $this; }
}
