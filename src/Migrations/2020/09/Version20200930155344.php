<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200930155344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE schedule_date ADD vehicle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schedule_date ADD CONSTRAINT FK_4AD61721545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('CREATE INDEX IDX_4AD61721545317D1 ON schedule_date (vehicle_id)');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality ENUM(\'TRIP\', \'MOVEMENT\', \'RESERVED\'), CHANGE week_interval week_interval ENUM(\'WEEKDAY\', \'SATURDAY\', \'SUNDAY\')');
        $this->addSql('ALTER TABLE line CHANGE direction direction ENUM(\'GOING\', \'RETURN\', \'CIRCULATE\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE line CHANGE direction direction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE week_interval week_interval VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule_date DROP FOREIGN KEY FK_4AD61721545317D1');
        $this->addSql('DROP INDEX IDX_4AD61721545317D1 ON schedule_date');
        $this->addSql('ALTER TABLE schedule_date DROP vehicle_id');
    }
}
