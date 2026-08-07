<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260806201217 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piezas ADD precio_material NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE presupuesto ADD costo_operador NUMERIC(10, 2) DEFAULT NULL, ADD costo_maquina NUMERIC(10, 2) DEFAULT NULL, ADD tasa_fallo_global NUMERIC(10, 2) DEFAULT NULL, ADD tiempo_setup INT DEFAULT NULL, ADD margen_ganancia NUMERIC(10, 2) DEFAULT NULL, ADD tiempo_post_procesado INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piezas DROP precio_material');
        $this->addSql('ALTER TABLE presupuesto DROP costo_operador, DROP costo_maquina, DROP tasa_fallo_global, DROP tiempo_setup, DROP margen_ganancia, DROP tiempo_post_procesado');
    }
}
