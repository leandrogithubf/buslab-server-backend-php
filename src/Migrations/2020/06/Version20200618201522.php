<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200618201522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE parameter_configuration CHANGE green_belt green_belt VARCHAR(255) DEFAULT NULL, CHANGE red_belt red_belt VARCHAR(255) DEFAULT NULL, CHANGE max_speed max_speed VARCHAR(255) DEFAULT NULL, CHANGE max_years_op max_years_op VARCHAR(255) DEFAULT NULL, CHANGE acc_max_sprint acc_max_sprint VARCHAR(255) DEFAULT NULL, CHANGE acc_max_motion acc_max_motion VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE parameter_configuration CHANGE green_belt green_belt INT DEFAULT NULL, CHANGE red_belt red_belt INT DEFAULT NULL, CHANGE max_speed max_speed INT DEFAULT NULL, CHANGE max_years_op max_years_op INT DEFAULT NULL, CHANGE acc_max_sprint acc_max_sprint INT DEFAULT NULL, CHANGE acc_max_motion acc_max_motion INT DEFAULT NULL');
    }
}
