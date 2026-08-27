<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260821200027 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE presupuesto ADD cliente_id INT DEFAULT NULL, ADD producto_id INT DEFAULT NULL, ADD cantidad_global INT NOT NULL, ADD delivery NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE presupuesto ADD CONSTRAINT FK_1B6368D3DE734E51 FOREIGN KEY (cliente_id) REFERENCES cliente (id)');
        $this->addSql('ALTER TABLE presupuesto ADD CONSTRAINT FK_1B6368D37645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('CREATE INDEX IDX_1B6368D3DE734E51 ON presupuesto (cliente_id)');
        $this->addSql('CREATE INDEX IDX_1B6368D37645698E ON presupuesto (producto_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE presupuesto DROP FOREIGN KEY FK_1B6368D3DE734E51');
        $this->addSql('ALTER TABLE presupuesto DROP FOREIGN KEY FK_1B6368D37645698E');
        $this->addSql('DROP INDEX IDX_1B6368D3DE734E51 ON presupuesto');
        $this->addSql('DROP INDEX IDX_1B6368D37645698E ON presupuesto');
        $this->addSql('ALTER TABLE presupuesto DROP cliente_id, DROP producto_id, DROP cantidad_global, DROP delivery');
    }
}
