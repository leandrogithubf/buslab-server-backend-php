<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200809210857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renames the descrption fields of the tirp status and modality';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trip_status ADD description VARCHAR(120) NOT NULL');
        $this->addSql('UPDATE trip_status set description = status');
        $this->addSql('ALTER TABLE trip_status DROP status');

        $this->addSql('ALTER TABLE trip_modality ADD description VARCHAR(120) NOT NULL');
        $this->addSql('UPDATE trip_modality set description = modality');
        $this->addSql('ALTER TABLE trip_modality DROP modality');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trip_modality ADD modality VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP description');
        $this->addSql('ALTER TABLE trip_status ADD status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP description');
    }
}
