<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901120000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Inventario: proveedores, compras, ventas, stock, movimientos y desacople';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE activo ADD cantidad_reservada NUMERIC(10, 2) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE producto ADD cantidad_stock NUMERIC(10, 2) DEFAULT 0 NOT NULL');
        $this->addSql("ALTER TABLE presupuesto ADD estado VARCHAR(30) DEFAULT 'borrador' NOT NULL");

        $this->addSql('CREATE TABLE proveedor (id INT AUTO_INCREMENT NOT NULL, empresa_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, rif VARCHAR(50) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, telefono VARCHAR(50) DEFAULT NULL, direccion VARCHAR(1000) DEFAULT NULL, contacto VARCHAR(255) DEFAULT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, INDEX IDX_C3C95C69521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compra (id INT AUTO_INCREMENT NOT NULL, proveedor_id INT DEFAULT NULL, empresa_id INT DEFAULT NULL, numero VARCHAR(50) NOT NULL, fecha DATE NOT NULL, observacion VARCHAR(1000) DEFAULT NULL, total NUMERIC(12, 2) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, INDEX IDX_9EC131EFCB305D73 (proveedor_id), INDEX IDX_9EC131EF521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compra_linea (id INT AUTO_INCREMENT NOT NULL, compra_id INT DEFAULT NULL, activo_id INT DEFAULT NULL, cantidad NUMERIC(10, 2) NOT NULL, valor_unitario NUMERIC(10, 2) NOT NULL, INDEX IDX_E1D437C5F2D19116 (compra_id), INDEX IDX_E1D437C5DAC0C129 (activo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venta (id INT AUTO_INCREMENT NOT NULL, presupuesto_id INT DEFAULT NULL, cliente_id INT DEFAULT NULL, producto_id INT DEFAULT NULL, empresa_id INT DEFAULT NULL, numero VARCHAR(50) NOT NULL, fecha DATE NOT NULL, descripcion VARCHAR(1000) DEFAULT NULL, cantidad NUMERIC(10, 2) NOT NULL, total NUMERIC(12, 2) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, INDEX IDX_8FE7EE55168190E2 (presupuesto_id), INDEX IDX_8FE7EE55DE734D2D (cliente_id), INDEX IDX_8FE7EE55764568D8 (producto_id), INDEX IDX_8FE7EE55521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movimiento_inventario (id INT AUTO_INCREMENT NOT NULL, activo_id INT DEFAULT NULL, producto_id INT DEFAULT NULL, empresa_id INT DEFAULT NULL, tipo VARCHAR(30) NOT NULL, cantidad NUMERIC(10, 2) NOT NULL, referencia_tipo VARCHAR(50) DEFAULT NULL, referencia_id INT DEFAULT NULL, observacion VARCHAR(1000) DEFAULT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, INDEX IDX_MOV_ACTIVO (activo_id), INDEX IDX_MOV_PROD (producto_id), INDEX IDX_MOV_EMP (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE desacople (id INT AUTO_INCREMENT NOT NULL, producto_id INT DEFAULT NULL, empresa_id INT DEFAULT NULL, cantidad_producto NUMERIC(10, 2) NOT NULL, observacion VARCHAR(1000) DEFAULT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) NOT NULL, INDEX IDX_DES_PROD (producto_id), INDEX IDX_DES_EMP (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE desacople_linea (id INT AUTO_INCREMENT NOT NULL, desacople_id INT DEFAULT NULL, activo_id INT DEFAULT NULL, recuperado NUMERIC(10, 2) NOT NULL, merma NUMERIC(10, 2) NOT NULL, INDEX IDX_DL_DES (desacople_id), INDEX IDX_DL_ACT (activo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE desacople_linea');
        $this->addSql('DROP TABLE desacople');
        $this->addSql('DROP TABLE movimiento_inventario');
        $this->addSql('DROP TABLE venta');
        $this->addSql('DROP TABLE compra_linea');
        $this->addSql('DROP TABLE compra');
        $this->addSql('DROP TABLE proveedor');
        $this->addSql('ALTER TABLE activo DROP cantidad_reservada');
        $this->addSql('ALTER TABLE producto DROP cantidad_stock');
        $this->addSql('ALTER TABLE presupuesto DROP estado');
    }
}
