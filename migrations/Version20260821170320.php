<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260821170320 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piezas ADD producto_id INT DEFAULT NULL, ADD activo_id INT DEFAULT NULL, ADD maquina_id INT DEFAULT NULL, ADD tipo VARCHAR(100) DEFAULT NULL, ADD cantidad INT NOT NULL');
        $this->addSql('ALTER TABLE piezas ADD CONSTRAINT FK_CEB629897645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE piezas ADD CONSTRAINT FK_CEB62989487E62A3 FOREIGN KEY (activo_id) REFERENCES activo (id)');
        $this->addSql('ALTER TABLE piezas ADD CONSTRAINT FK_CEB6298941420729 FOREIGN KEY (maquina_id) REFERENCES activo (id)');
        $this->addSql('CREATE INDEX IDX_CEB629897645698E ON piezas (producto_id)');
        $this->addSql('CREATE INDEX IDX_CEB62989487E62A3 ON piezas (activo_id)');
        $this->addSql('CREATE INDEX IDX_CEB6298941420729 ON piezas (maquina_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piezas DROP FOREIGN KEY FK_CEB629897645698E');
        $this->addSql('ALTER TABLE piezas DROP FOREIGN KEY FK_CEB62989487E62A3');
        $this->addSql('ALTER TABLE piezas DROP FOREIGN KEY FK_CEB6298941420729');
        $this->addSql('DROP INDEX IDX_CEB629897645698E ON piezas');
        $this->addSql('DROP INDEX IDX_CEB62989487E62A3 ON piezas');
        $this->addSql('DROP INDEX IDX_CEB6298941420729 ON piezas');
        $this->addSql('ALTER TABLE piezas DROP producto_id, DROP activo_id, DROP maquina_id, DROP tipo, DROP cantidad');
    }
}
