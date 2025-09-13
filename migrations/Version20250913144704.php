<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250913144704 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parametro (id INT AUTO_INCREMENT NOT NULL, perfil_id INT DEFAULT NULL, unidad VARCHAR(50) NOT NULL, tipo VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, prod_max_horas INT NOT NULL, horas_max INT NOT NULL, horas_uso INT NOT NULL, INDEX IDX_4C12795F57291544 (perfil_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE perfil (id INT AUTO_INCREMENT NOT NULL, empresa_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, tipo VARCHAR(100) NOT NULL, sector VARCHAR(100) NOT NULL, empleados INT NOT NULL, rif VARCHAR(20) NOT NULL, periodo VARCHAR(20) NOT NULL, direccion VARCHAR(1000) NOT NULL, moneda VARCHAR(20) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, update_by VARCHAR(50) DEFAULT NULL, update_at DATETIME DEFAULT NULL, INDEX IDX_96657647521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parametro ADD CONSTRAINT FK_4C12795F57291544 FOREIGN KEY (perfil_id) REFERENCES perfil (id)');
        $this->addSql('ALTER TABLE perfil ADD CONSTRAINT FK_96657647521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id)');
        $this->addSql('ALTER TABLE cargo DROP tipo');
        $this->addSql('ALTER TABLE coordinacion DROP created_at');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parametro DROP FOREIGN KEY FK_4C12795F57291544');
        $this->addSql('DROP TABLE parametro');
        $this->addSql('DROP TABLE perfil');
        $this->addSql('ALTER TABLE cargo ADD tipo INT DEFAULT NULL');
        $this->addSql('ALTER TABLE coordinacion ADD created_at DATETIME DEFAULT NULL');
    }
}
