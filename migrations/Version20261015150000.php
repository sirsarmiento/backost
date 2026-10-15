<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Familia Sin familia, multitecnología, tasas por tecnología y horas reales por pieza';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'SF', 'Sin familia', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'SF')");

        $this->addSql('ALTER TABLE producto MODIFY sku VARCHAR(80) DEFAULT NULL, MODIFY tecnologia VARCHAR(80) DEFAULT NULL, MODIFY material VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE producto ADD multitecnologia TINYINT(1) DEFAULT 0 NOT NULL, ADD tasas_fallo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE presupuesto ADD multitecnologia TINYINT(1) DEFAULT 0 NOT NULL, ADD tasas_fallo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE piezas_producto ADD tecnologia VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE piezas ADD tecnologia VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE piezas DROP tecnologia');
        $this->addSql('ALTER TABLE piezas_producto DROP tecnologia');
        $this->addSql('ALTER TABLE presupuesto DROP multitecnologia, DROP tasas_fallo');
        $this->addSql('ALTER TABLE producto DROP multitecnologia, DROP tasas_fallo');
        $this->addSql('ALTER TABLE producto MODIFY sku VARCHAR(50) DEFAULT NULL, MODIFY tecnologia VARCHAR(10) DEFAULT NULL, MODIFY material VARCHAR(10) DEFAULT NULL');
        $this->addSql("DELETE FROM familia WHERE codigo = 'SF'");
    }
}
