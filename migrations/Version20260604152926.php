<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604152926 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE familia (id INT AUTO_INCREMENT NOT NULL, empresa_id INT DEFAULT NULL, codigo VARCHAR(10) NOT NULL, nombre VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) DEFAULT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, INDEX IDX_5E69C24F521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_familia (id INT AUTO_INCREMENT NOT NULL, familia_id INT NOT NULL, codigo VARCHAR(10) NOT NULL, nombre VARCHAR(255) NOT NULL, INDEX IDX_21B19EBBD02563A3 (familia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE familia ADD CONSTRAINT FK_5E69C24F521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id)');
        $this->addSql('ALTER TABLE sub_familia ADD CONSTRAINT FK_21B19EBBD02563A3 FOREIGN KEY (familia_id) REFERENCES familia (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sub_familia DROP FOREIGN KEY FK_21B19EBBD02563A3');
        $this->addSql('DROP TABLE familia');
        $this->addSql('DROP TABLE sub_familia');
    }
}
