<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261117120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Campos de pieza por tecnología, sectores de cliente, estado de venta y catálogo CNC/láser/chocolate/alimentos';
    }

    public function up(Schema $schema): void
    {
        foreach (['piezas', 'piezas_producto'] as $tabla) {
            $this->addSql("ALTER TABLE $tabla ADD ancho_cm NUMERIC(10, 2) DEFAULT NULL, ADD largo_cm NUMERIC(10, 2) DEFAULT NULL, ADD area_cm2 NUMERIC(10, 2) DEFAULT NULL, ADD caras_pcb INT DEFAULT NULL, ADD mascara_uv_soldadura TINYINT(1) DEFAULT NULL, ADD mascara_uv_textos TINYINT(1) DEFAULT NULL, ADD descartables_pct NUMERIC(10, 2) DEFAULT NULL, ADD limpieza_maquina NUMERIC(10, 2) DEFAULT NULL, ADD empaque NUMERIC(10, 2) DEFAULT NULL, ADD material_libre VARCHAR(255) DEFAULT NULL, ADD costo_estimado NUMERIC(12, 2) DEFAULT NULL");
        }

        $this->addSql("ALTER TABLE cliente ADD sectores LONGTEXT DEFAULT NULL");
        $this->addSql("ALTER TABLE venta ADD estado VARCHAR(30) DEFAULT 'PENDIENTE'");
        $this->addSql("UPDATE venta SET estado = 'PENDIENTE' WHERE estado IS NULL OR estado = ''");

        $this->addSql("INSERT INTO tecnologia (codigo, nombre, create_at, create_by) SELECT 'CNC', 'Control numérico', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tecnologia WHERE codigo = 'CNC')");
        $this->addSql("INSERT INTO tecnologia (codigo, nombre, create_at, create_by) SELECT 'LSR', 'Corte láser', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tecnologia WHERE codigo = 'LSR')");
        $this->addSql("INSERT INTO tecnologia (codigo, nombre, create_at, create_by) SELECT 'CHO', 'Chocolate', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tecnologia WHERE codigo = 'CHO')");
        $this->addSql("INSERT INTO tecnologia (codigo, nombre, create_at, create_by) SELECT 'ALM', 'Alimentos', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tecnologia WHERE codigo = 'ALM')");

        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'PCB', 'Circuito impreso', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'PCB')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'ACR', 'Acrílico negro', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'ACR')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'CUE', 'Cuero', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'CUE')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'CAR', 'Cartón', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'CAR')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'CTU', 'Cartulina', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'CTU')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'VIN', 'Vinilo', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'VIN')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'CHO', 'Chocolate', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'CHO')");

        $this->addSql("INSERT INTO tecnologia_material (tecnologia_id, material_catalogo_id) SELECT t.id, m.id FROM tecnologia t, material_catalogo m WHERE t.codigo = 'CNC' AND m.codigo = 'PCB' AND NOT EXISTS (SELECT 1 FROM tecnologia_material tm WHERE tm.tecnologia_id = t.id AND tm.material_catalogo_id = m.id)");
        $this->addSql("INSERT INTO tecnologia_material (tecnologia_id, material_catalogo_id) SELECT t.id, m.id FROM tecnologia t, material_catalogo m WHERE t.codigo = 'LSR' AND m.codigo IN ('ACR','CUE','CAR','CTU','VIN') AND NOT EXISTS (SELECT 1 FROM tecnologia_material tm WHERE tm.tecnologia_id = t.id AND tm.material_catalogo_id = m.id)");
        $this->addSql("INSERT INTO tecnologia_material (tecnologia_id, material_catalogo_id) SELECT t.id, m.id FROM tecnologia t, material_catalogo m WHERE t.codigo = 'CHO' AND m.codigo = 'CHO' AND NOT EXISTS (SELECT 1 FROM tecnologia_material tm WHERE tm.tecnologia_id = t.id AND tm.material_catalogo_id = m.id)");
    }

    public function down(Schema $schema): void
    {
        foreach (['piezas', 'piezas_producto'] as $tabla) {
            $this->addSql("ALTER TABLE $tabla DROP ancho_cm, DROP largo_cm, DROP area_cm2, DROP caras_pcb, DROP mascara_uv_soldadura, DROP mascara_uv_textos, DROP descartables_pct, DROP limpieza_maquina, DROP empaque, DROP material_libre, DROP costo_estimado");
        }
        $this->addSql('ALTER TABLE cliente DROP sectores');
        $this->addSql('ALTER TABLE venta DROP estado');
    }
}
