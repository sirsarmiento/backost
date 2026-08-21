<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260821155205 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE producto ADD tasa_fallo NUMERIC(10, 2) NOT NULL, ADD tiempo_setup NUMERIC(10, 2) NOT NULL, ADD post_procesado NUMERIC(10, 2) NOT NULL, ADD margen_ganancia NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE producto DROP tasa_fallo, DROP tiempo_setup, DROP post_procesado, DROP margen_ganancia');
    }
}
