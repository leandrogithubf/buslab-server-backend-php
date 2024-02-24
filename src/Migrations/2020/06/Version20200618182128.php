<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200618182128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE parameter_configuration (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, green_belt INT DEFAULT NULL, red_belt INT DEFAULT NULL, max_speed INT DEFAULT NULL, max_years_op INT DEFAULT NULL, idleness VARCHAR(255) DEFAULT NULL, delay_tolerance_start VARCHAR(255) DEFAULT NULL, delay_tolerance_end VARCHAR(255) DEFAULT NULL, acc_max_sprint INT DEFAULT NULL, acc_max_motion INT DEFAULT NULL, identifier VARCHAR(15) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D98F3F1D979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parameter_configuration ADD CONSTRAINT FK_D98F3F1D979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE parameter_configuration');
    }
}
