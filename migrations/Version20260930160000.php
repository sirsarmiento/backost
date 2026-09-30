<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260930160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tecnología en activo fijo (equipos de fabricación)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activo ADD tecnologia VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activo DROP tecnologia');
    }
}
