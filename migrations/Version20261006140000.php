<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261006140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Horas de impresión en producto y presupuesto; marca y color en activo; tablas maestras de marca y color';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE producto ADD horas_impresion INT DEFAULT 0 NOT NULL, ADD minutos_impresion INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE presupuesto ADD horas_impresion INT DEFAULT 0 NOT NULL, ADD minutos_impresion INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE activo ADD marca VARCHAR(100) DEFAULT NULL, ADD color VARCHAR(100) DEFAULT NULL');

        $this->addSql('CREATE TABLE marca (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) DEFAULT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE color_catalogo (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) DEFAULT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql("INSERT INTO marca (nombre, create_at, create_by) VALUES ('Overtour', NOW(), 'system'), ('Politra', NOW(), 'system'), ('Guimodor', NOW(), 'system'), ('Rebel', NOW(), 'system'), ('Creimatx', NOW(), 'system')");
        $this->addSql("INSERT INTO color_catalogo (nombre, create_at, create_by) VALUES ('Negro', NOW(), 'system'), ('Blanco', NOW(), 'system'), ('Gris', NOW(), 'system'), ('Rojo', NOW(), 'system'), ('Azul', NOW(), 'system'), ('Amarillo', NOW(), 'system'), ('Verde', NOW(), 'system'), ('Transparente', NOW(), 'system'), ('Natural', NOW(), 'system')");

        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'PETG', 'PETG', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'PETG')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'TPU', 'TPU', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'TPU')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) SELECT 'ASA', 'ASA', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM material_catalogo WHERE codigo = 'ASA')");
        $this->addSql("INSERT INTO tecnologia_material (tecnologia_id, material_catalogo_id) SELECT t.id, m.id FROM tecnologia t, material_catalogo m WHERE t.codigo = 'FDM' AND m.codigo IN ('PETG','TPU','ASA') AND NOT EXISTS (SELECT 1 FROM tecnologia_material tm WHERE tm.tecnologia_id = t.id AND tm.material_catalogo_id = m.id)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE producto DROP horas_impresion, DROP minutos_impresion');
        $this->addSql('ALTER TABLE presupuesto DROP horas_impresion, DROP minutos_impresion');
        $this->addSql('ALTER TABLE activo DROP marca, DROP color');
        $this->addSql('DROP TABLE marca');
        $this->addSql('DROP TABLE color_catalogo');
    }
}
