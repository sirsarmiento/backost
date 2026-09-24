<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Código de catálogo en producto + tecnologías y materiales configurables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE producto ADD codigo_catalogo VARCHAR(30) DEFAULT NULL, ADD tecnologia VARCHAR(10) DEFAULT NULL, ADD material VARCHAR(10) DEFAULT NULL, ADD familia_id INT DEFAULT NULL, ADD correlativo VARCHAR(10) DEFAULT NULL, ADD serie VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB061587E5D3D5 FOREIGN KEY (familia_id) REFERENCES familia (id)');
        $this->addSql('CREATE INDEX IDX_A7BB061587E5D3D5 ON producto (familia_id)');

        $this->addSql('CREATE TABLE tecnologia (id INT AUTO_INCREMENT NOT NULL, codigo VARCHAR(10) NOT NULL, nombre VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) DEFAULT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material_catalogo (id INT AUTO_INCREMENT NOT NULL, codigo VARCHAR(10) NOT NULL, nombre VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL, create_by VARCHAR(50) DEFAULT NULL, update_at DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tecnologia_material (tecnologia_id INT NOT NULL, material_catalogo_id INT NOT NULL, INDEX IDX_TEC_MAT_TEC (tecnologia_id), INDEX IDX_TEC_MAT_MAT (material_catalogo_id), PRIMARY KEY(tecnologia_id, material_catalogo_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tecnologia_material ADD CONSTRAINT FK_TEC_MAT_TEC FOREIGN KEY (tecnologia_id) REFERENCES tecnologia (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tecnologia_material ADD CONSTRAINT FK_TEC_MAT_MAT FOREIGN KEY (material_catalogo_id) REFERENCES material_catalogo (id) ON DELETE CASCADE');

        $this->addSql("INSERT INTO tecnologia (codigo, nombre, create_at, create_by) VALUES ('FDM', 'Filamento', NOW(), 'system'), ('SLA', 'Resina', NOW(), 'system')");
        $this->addSql("INSERT INTO material_catalogo (codigo, nombre, create_at, create_by) VALUES ('PLA', 'Ácido Poliláctico', NOW(), 'system'), ('ABS', 'Acrilonitrilo Butadieno Estireno', NOW(), 'system'), ('PET', 'Polietileno Tereftalato', NOW(), 'system'), ('RES', 'Resina', NOW(), 'system')");
        $this->addSql("INSERT INTO tecnologia_material (tecnologia_id, material_catalogo_id) SELECT t.id, m.id FROM tecnologia t, material_catalogo m WHERE (t.codigo = 'FDM' AND m.codigo IN ('PLA','ABS','PET')) OR (t.codigo = 'SLA' AND m.codigo = 'RES')");

        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'LUD', 'Lúdico / Educativo', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'LUD')");
        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'POP', 'Productos POP', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'POP')");
        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'ROB', 'Robótica', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'ROB')");
        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'MLD', 'Molde', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'MLD')");
        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'MED', 'Médico', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'MED')");
        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'ODO', 'Odontológico', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'ODO')");
        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'AUT', 'Automotriz', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'AUT')");
        $this->addSql("INSERT INTO familia (codigo, nombre, create_at, create_by) SELECT 'IND', 'Industrial', NOW(), 'system' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM familia WHERE codigo = 'IND')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB061587E5D3D5');
        $this->addSql('DROP INDEX IDX_A7BB061587E5D3D5 ON producto');
        $this->addSql('ALTER TABLE producto DROP codigo_catalogo, DROP tecnologia, DROP material, DROP familia_id, DROP correlativo, DROP serie');
        $this->addSql('ALTER TABLE tecnologia_material DROP FOREIGN KEY FK_TEC_MAT_TEC');
        $this->addSql('ALTER TABLE tecnologia_material DROP FOREIGN KEY FK_TEC_MAT_MAT');
        $this->addSql('DROP TABLE tecnologia_material');
        $this->addSql('DROP TABLE material_catalogo');
        $this->addSql('DROP TABLE tecnologia');
    }
}
