<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923171924 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE producto ADD perfil_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB061557291544 FOREIGN KEY (perfil_id) REFERENCES perfil (id)');
        $this->addSql('CREATE INDEX IDX_A7BB061557291544 ON producto (perfil_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB061557291544');
        $this->addSql('DROP INDEX IDX_A7BB061557291544 ON producto');
        $this->addSql('ALTER TABLE producto DROP perfil_id');
    }
}
