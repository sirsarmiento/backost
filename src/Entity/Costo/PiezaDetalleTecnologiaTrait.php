<?php

namespace App\Entity\Costo;

use Doctrine\ORM\Mapping as ORM;

trait PiezaDetalleTecnologiaTrait
{
    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $anchoCm;

    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $largoCm;

    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $areaCm2;

    /** @ORM\Column(type="integer", nullable=true) */
    private $carasPcb;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $mascaraUvSoldadura;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $mascaraUvTextos;

    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $descartablesPct;

    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $limpiezaMaquina;

    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $empaque;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $materialLibre;

    /** @ORM\Column(type="decimal", precision=12, scale=2, nullable=true) */
    private $costoEstimado;

    public function getAnchoCm() { return $this->anchoCm; }
    public function setAnchoCm($anchoCm): self { $this->anchoCm = $anchoCm; return $this; }
    public function getLargoCm() { return $this->largoCm; }
    public function setLargoCm($largoCm): self { $this->largoCm = $largoCm; return $this; }
    public function getAreaCm2() { return $this->areaCm2; }
    public function setAreaCm2($areaCm2): self { $this->areaCm2 = $areaCm2; return $this; }
    public function getCarasPcb(): ?int { return $this->carasPcb; }
    public function setCarasPcb(?int $carasPcb): self { $this->carasPcb = $carasPcb; return $this; }
    public function getMascaraUvSoldadura(): ?bool { return $this->mascaraUvSoldadura; }
    public function setMascaraUvSoldadura(?bool $mascaraUvSoldadura): self { $this->mascaraUvSoldadura = $mascaraUvSoldadura; return $this; }
    public function getMascaraUvTextos(): ?bool { return $this->mascaraUvTextos; }
    public function setMascaraUvTextos(?bool $mascaraUvTextos): self { $this->mascaraUvTextos = $mascaraUvTextos; return $this; }
    public function getDescartablesPct() { return $this->descartablesPct; }
    public function setDescartablesPct($descartablesPct): self { $this->descartablesPct = $descartablesPct; return $this; }
    public function getLimpiezaMaquina() { return $this->limpiezaMaquina; }
    public function setLimpiezaMaquina($limpiezaMaquina): self { $this->limpiezaMaquina = $limpiezaMaquina; return $this; }
    public function getEmpaque() { return $this->empaque; }
    public function setEmpaque($empaque): self { $this->empaque = $empaque; return $this; }
    public function getMaterialLibre(): ?string { return $this->materialLibre; }
    public function setMaterialLibre(?string $materialLibre): self { $this->materialLibre = $materialLibre; return $this; }
    public function getCostoEstimado() { return $this->costoEstimado; }
    public function setCostoEstimado($costoEstimado): self { $this->costoEstimado = $costoEstimado; return $this; }
}
