<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220411161603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE checkpoint DROP engine_percent_load_current_speed, DROP remote_accelerator_pedal_position, DROP accelerator_pedal_position_two, DROP vehicle_acceleration_rate_limit_status, DROP actual_maximum_available_engine, DROP driver_one_working_state, DROP driver_two_working_state, DROP vehicle_motion, DROP driver_one_time_related_states, DROP driver_card_one, DROP vehicle_overspeed, DROP driver_two_time_related_states, DROP driver_card_driver_two, DROP system_event, DROP handling_information, DROP tachograph_performance, DROP direction_indicator, DROP tachograph_output_shaft_speed');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
