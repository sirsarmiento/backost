<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260623161831 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activo ADD categoria VARCHAR(255) DEFAULT NULL, ADD sub_categoria VARCHAR(255) DEFAULT NULL, ADD consumo_maquina NUMERIC(10, 2) NOT NULL, ADD tarifa NUMERIC(10, 2) NOT NULL, ADD costo_mantenimiento NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activo DROP categoria, DROP sub_categoria, DROP consumo_maquina, DROP tarifa, DROP costo_mantenimiento');
    }
}
