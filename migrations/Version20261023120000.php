<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261023120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Líneas de presupuesto para varios productos o servicios en una misma solicitud';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE presupuesto ADD lineas LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE presupuesto DROP lineas');
    }
}
