<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250918015526 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activo (id INT AUTO_INCREMENT NOT NULL, empresa_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, costo_inicial NUMERIC(10, 2) NOT NULL, valor_residual NUMERIC(10, 2) NOT NULL, vida_util INT NOT NULL, fecha_compra DATE DEFAULT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, INDEX IDX_ABCB151C521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activo ADD CONSTRAINT FK_ABCB151C521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activo');
    }
}
