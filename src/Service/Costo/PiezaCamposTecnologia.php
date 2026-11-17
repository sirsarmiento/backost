<?php

namespace App\Service\Costo;

class PiezaCamposTecnologia
{
    public static function aplicar($pieza, array $data): void
    {
        $ancho = self::num($data, ['anchoCm', 'ancho_cm']);
        $largo = self::num($data, ['largoCm', 'largo_cm']);
        $area = self::num($data, ['areaCm2', 'area_cm2']);
        if ($area === null && $ancho !== null && $largo !== null) {
            $area = round($ancho * $largo, 2);
        }
        if (self::tiene($data, ['anchoCm', 'ancho_cm'])) {
            $pieza->setAnchoCm($ancho);
        }
        if (self::tiene($data, ['largoCm', 'largo_cm'])) {
            $pieza->setLargoCm($largo);
        }
        if ($area !== null || self::tiene($data, ['areaCm2', 'area_cm2'])) {
            $pieza->setAreaCm2($area);
        }
        if (self::tiene($data, ['carasPcb', 'caras_pcb'])) {
            $pieza->setCarasPcb((int) self::num($data, ['carasPcb', 'caras_pcb']));
        }
        if (self::tiene($data, ['mascaraUvSoldadura', 'mascara_uv_soldadura'])) {
            $pieza->setMascaraUvSoldadura(self::bool($data, ['mascaraUvSoldadura', 'mascara_uv_soldadura']));
        }
        if (self::tiene($data, ['mascaraUvTextos', 'mascara_uv_textos'])) {
            $pieza->setMascaraUvTextos(self::bool($data, ['mascaraUvTextos', 'mascara_uv_textos']));
        }
        if (self::tiene($data, ['descartablesPct', 'descartables_pct'])) {
            $pieza->setDescartablesPct(self::num($data, ['descartablesPct', 'descartables_pct']));
        }
        if (self::tiene($data, ['limpiezaMaquina', 'limpieza_maquina'])) {
            $pieza->setLimpiezaMaquina(self::num($data, ['limpiezaMaquina', 'limpieza_maquina']));
        }
        if (self::tiene($data, ['empaque'])) {
            $pieza->setEmpaque(self::num($data, ['empaque']));
        }
        if (self::tiene($data, ['materialLibre', 'material_libre'])) {
            $pieza->setMaterialLibre(self::texto($data, ['materialLibre', 'material_libre']));
        }
        if (self::tiene($data, ['costoEstimado', 'costo_estimado'])) {
            $pieza->setCostoEstimado(self::num($data, ['costoEstimado', 'costo_estimado']));
        }
    }

    public static function extraer($pieza): array
    {
        $maquinaNombre = $pieza->getMaquina() ? $pieza->getMaquina()->getNombre() : null;
        $materialNombre = $pieza->getActivo() ? $pieza->getActivo()->getNombre() : ($pieza->getMaterialLibre() ?: null);
        return [
            'anchoCm' => $pieza->getAnchoCm(),
            'largoCm' => $pieza->getLargoCm(),
            'areaCm2' => $pieza->getAreaCm2(),
            'carasPcb' => $pieza->getCarasPcb(),
            'mascaraUvSoldadura' => $pieza->getMascaraUvSoldadura(),
            'mascaraUvTextos' => $pieza->getMascaraUvTextos(),
            'descartablesPct' => $pieza->getDescartablesPct(),
            'limpiezaMaquina' => $pieza->getLimpiezaMaquina(),
            'empaque' => $pieza->getEmpaque(),
            'materialLibre' => $pieza->getMaterialLibre(),
            'costoEstimado' => $pieza->getCostoEstimado(),
            'maquinaNombre' => $maquinaNombre,
            'materialNombre' => $materialNombre,
            'MaquinaNombre' => $maquinaNombre,
            'MaterialNombre' => $materialNombre,
        ];
    }

    private static function tiene(array $data, array $claves): bool
    {
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $data)) {
                return true;
            }
        }
        return false;
    }

    private static function num(array $data, array $claves): ?float
    {
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $data) && $data[$clave] !== '' && $data[$clave] !== null) {
                return (float) $data[$clave];
            }
        }
        return null;
    }

    private static function texto(array $data, array $claves): ?string
    {
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $data) && $data[$clave] !== null) {
                $valor = trim((string) $data[$clave]);
                return $valor === '' ? null : $valor;
            }
        }
        return null;
    }

    private static function bool(array $data, array $claves): bool
    {
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $data)) {
                return filter_var($data[$clave], FILTER_VALIDATE_BOOLEAN);
            }
        }
        return false;
    }
}
