<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807144714 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activo ADD tipo VARCHAR(50) NOT NULL, ADD cantidad NUMERIC(10, 2) NOT NULL, ADD unidad_medida VARCHAR(100) DEFAULT NULL, ADD presentacion VARCHAR(255) DEFAULT NULL, ADD descripcion VARCHAR(1000) DEFAULT NULL, ADD ubicacion VARCHAR(255) DEFAULT NULL, ADD valor_unitario NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activo DROP tipo, DROP cantidad, DROP unidad_medida, DROP presentacion, DROP descripcion, DROP ubicacion, DROP valor_unitario');
    }
}
