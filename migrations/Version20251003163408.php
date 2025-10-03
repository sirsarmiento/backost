<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251003163408 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE piezas (id INT AUTO_INCREMENT NOT NULL, presupuesto_id INT DEFAULT NULL, nombre VARCHAR(255) DEFAULT NULL, gramos NUMERIC(10, 2) NOT NULL, metros NUMERIC(10, 2) NOT NULL, horas INT NOT NULL, minutos INT NOT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, INDEX IDX_CEB6298990119F0F (presupuesto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE presupuesto (id INT AUTO_INCREMENT NOT NULL, empresa_id INT DEFAULT NULL, clasificacion VARCHAR(100) NOT NULL, descripcion VARCHAR(1000) NOT NULL, numero VARCHAR(50) NOT NULL, fecha DATE NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, INDEX IDX_1B6368D3521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE piezas ADD CONSTRAINT FK_CEB6298990119F0F FOREIGN KEY (presupuesto_id) REFERENCES presupuesto (id)');
        $this->addSql('ALTER TABLE presupuesto ADD CONSTRAINT FK_1B6368D3521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piezas DROP FOREIGN KEY FK_CEB6298990119F0F');
        $this->addSql('DROP TABLE piezas');
        $this->addSql('DROP TABLE presupuesto');
    }
}
