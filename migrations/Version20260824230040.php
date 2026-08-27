<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260824230040 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE piezas_producto (id INT AUTO_INCREMENT NOT NULL, producto_id INT DEFAULT NULL, activo_id INT DEFAULT NULL, maquina_id INT DEFAULT NULL, nombre VARCHAR(255) DEFAULT NULL, gramos NUMERIC(10, 2) NOT NULL, metros NUMERIC(10, 2) NOT NULL, horas INT NOT NULL, minutos INT NOT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, precio_material NUMERIC(10, 2) NOT NULL, tipo VARCHAR(100) DEFAULT NULL, cantidad INT NOT NULL, INDEX IDX_CDF389427645698E (producto_id), INDEX IDX_CDF38942487E62A3 (activo_id), INDEX IDX_CDF3894241420729 (maquina_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE piezas_producto ADD CONSTRAINT FK_CDF389427645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE piezas_producto ADD CONSTRAINT FK_CDF38942487E62A3 FOREIGN KEY (activo_id) REFERENCES activo (id)');
        $this->addSql('ALTER TABLE piezas_producto ADD CONSTRAINT FK_CDF3894241420729 FOREIGN KEY (maquina_id) REFERENCES activo (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE piezas_producto');
    }
}
