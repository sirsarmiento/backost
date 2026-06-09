<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609141311 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE codigo (id INT AUTO_INCREMENT NOT NULL, producto_id INT DEFAULT NULL, familia_id INT DEFAULT NULL, subfamilia_id INT DEFAULT NULL, categoria VARCHAR(255) NOT NULL, tecnologia VARCHAR(255) NOT NULL, material VARCHAR(255) NOT NULL, codigo VARCHAR(40) NOT NULL, catalogo VARCHAR(20) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, INDEX IDX_20332D997645698E (producto_id), INDEX IDX_20332D99D02563A3 (familia_id), INDEX IDX_20332D998FB48400 (subfamilia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE codigo ADD CONSTRAINT FK_20332D997645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE codigo ADD CONSTRAINT FK_20332D99D02563A3 FOREIGN KEY (familia_id) REFERENCES familia (id)');
        $this->addSql('ALTER TABLE codigo ADD CONSTRAINT FK_20332D998FB48400 FOREIGN KEY (subfamilia_id) REFERENCES sub_familia (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE codigo');
    }
}
