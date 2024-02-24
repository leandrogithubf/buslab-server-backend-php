<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622161527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint ADD engine_torque_mode INT DEFAULT NULL, ADD actuel_engine_high_resolution DOUBLE PRECISION DEFAULT NULL, ADD driver_demand INT DEFAULT NULL, ADD actual_engine INT DEFAULT NULL, ADD engine_speed DOUBLE PRECISION DEFAULT NULL, ADD source_address INT DEFAULT NULL, ADD engine_starter_mode INT DEFAULT NULL, ADD engine_demand INT DEFAULT NULL, ADD vehicle_speed DOUBLE PRECISION DEFAULT NULL, ADD hr_vehicle_distance DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE line CHANGE direction direction ENUM(\'GOING\', \'RETURN\', \'CIRCULATE\')');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality ENUM(\'TRIP\', \'MOVEMENT\', \'RESERVED\', \'STARTING_OPERATION\', \'CLOSING_OPERATION\'), CHANGE week_interval week_interval ENUM(\'WEEKDAY\', \'SATURDAY\', \'SUNDAY\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint DROP engine_torque_mode, DROP actuel_engine_high_resolution, DROP driver_demand, DROP actual_engine, DROP engine_speed, DROP source_address, DROP engine_starter_mode, DROP engine_demand, DROP vehicle_speed, DROP hr_vehicle_distance');
        $this->addSql('ALTER TABLE line CHANGE direction direction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE week_interval week_interval VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
