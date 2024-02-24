<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201016141721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event ADD driver_id INT DEFAULT NULL, ADD collector_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7C3423909 FOREIGN KEY (driver_id) REFERENCES employee (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7670BAFFE FOREIGN KEY (collector_id) REFERENCES employee (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C3423909 ON event (driver_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7670BAFFE ON event (collector_id)');
        $this->addSql('ALTER TABLE line CHANGE direction direction ENUM(\'GOING\', \'RETURN\', \'CIRCULATE\')');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality ENUM(\'TRIP\', \'MOVEMENT\', \'RESERVED\'), CHANGE week_interval week_interval ENUM(\'WEEKDAY\', \'SATURDAY\', \'SUNDAY\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7C3423909');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7670BAFFE');
        $this->addSql('DROP INDEX IDX_3BAE0AA7C3423909 ON event');
        $this->addSql('DROP INDEX IDX_3BAE0AA7670BAFFE ON event');
        $this->addSql('ALTER TABLE event DROP driver_id, DROP collector_id');
        $this->addSql('ALTER TABLE line CHANGE direction direction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE week_interval week_interval VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
