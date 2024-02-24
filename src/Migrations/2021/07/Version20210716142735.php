<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716142735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint ADD driver_one_working_state INT DEFAULT NULL, ADD driver_two_working_state INT DEFAULT NULL, ADD vehicle_motion INT DEFAULT NULL, ADD driver_one_time_related_states INT DEFAULT NULL, ADD driver_card_one INT DEFAULT NULL, ADD vehicle_overspeed INT DEFAULT NULL, ADD driver_two_time_related_states INT DEFAULT NULL, ADD driver_card_driver_two INT DEFAULT NULL, ADD system_event INT DEFAULT NULL, ADD handling_information INT DEFAULT NULL, ADD tachograph_performance INT DEFAULT NULL, ADD direction_indicator INT DEFAULT NULL, ADD tachograph_output_shaft_speed INT DEFAULT NULL, ADD high_resolution_trip_distance DOUBLE PRECISION DEFAULT NULL, ADD engine_fuel_temperature_one DOUBLE PRECISION DEFAULT NULL, ADD engine_oil_temperature_one DOUBLE PRECISION DEFAULT NULL, ADD engine_turbocharger_oil_temperature DOUBLE PRECISION DEFAULT NULL, ADD engine_intercooler_temperature DOUBLE PRECISION DEFAULT NULL, ADD engine_intercooler_thermostat_opening INT DEFAULT NULL, ADD accelerator_pedal_one_low_switch INT DEFAULT NULL, ADD accelerator_pedal_kickdown_switch INT DEFAULT NULL, ADD road_speed_limit_status INT DEFAULT NULL, ADD accelerator_pedal_two_low_switch INT DEFAULT NULL, ADD engine_percent_load_current_speed DOUBLE PRECISION DEFAULT NULL, ADD remote_accelerator_pedal_position DOUBLE PRECISION DEFAULT NULL, ADD accelerator_pedal_position_two DOUBLE PRECISION DEFAULT NULL, ADD vehicle_acceleration_rate_limit_status INT DEFAULT NULL, ADD actual_maximum_available_engine DOUBLE PRECISION DEFAULT NULL, ADD malfunction_indicator_lamp_status_one INT DEFAULT NULL, ADD red_stop_lamp_status_one INT DEFAULT NULL, ADD amber_warning_lamp_status_one INT DEFAULT NULL, ADD protect_lamp_status_one INT DEFAULT NULL, ADD spn_one INT DEFAULT NULL, ADD fmi_one INT DEFAULT NULL, ADD occurrence_count_one INT DEFAULT NULL, ADD malfunction_indicator_lamp_status_two INT DEFAULT NULL, ADD red_stop_lamp_status_two INT DEFAULT NULL, ADD amber_warning_lamp_status_two INT DEFAULT NULL, ADD protect_lamp_status_two INT DEFAULT NULL, ADD spn_two INT DEFAULT NULL, ADD fmi_two INT DEFAULT NULL, ADD occurrence_count_two INT DEFAULT NULL');
        $this->addSql('ALTER TABLE line CHANGE direction direction ENUM(\'GOING\', \'RETURN\', \'CIRCULATE\')');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality ENUM(\'TRIP\', \'MOVEMENT\', \'RESERVED\', \'STARTING_OPERATION\', \'CLOSING_OPERATION\'), CHANGE week_interval week_interval ENUM(\'WEEKDAY\', \'SATURDAY\', \'SUNDAY\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint DROP driver_one_working_state, DROP driver_two_working_state, DROP vehicle_motion, DROP driver_one_time_related_states, DROP driver_card_one, DROP vehicle_overspeed, DROP driver_two_time_related_states, DROP driver_card_driver_two, DROP system_event, DROP handling_information, DROP tachograph_performance, DROP direction_indicator, DROP tachograph_output_shaft_speed, DROP high_resolution_trip_distance, DROP engine_fuel_temperature_one, DROP engine_oil_temperature_one, DROP engine_turbocharger_oil_temperature, DROP engine_intercooler_temperature, DROP engine_intercooler_thermostat_opening, DROP accelerator_pedal_one_low_switch, DROP accelerator_pedal_kickdown_switch, DROP road_speed_limit_status, DROP accelerator_pedal_two_low_switch, DROP engine_percent_load_current_speed, DROP remote_accelerator_pedal_position, DROP accelerator_pedal_position_two, DROP vehicle_acceleration_rate_limit_status, DROP actual_maximum_available_engine, DROP malfunction_indicator_lamp_status_one, DROP red_stop_lamp_status_one, DROP amber_warning_lamp_status_one, DROP protect_lamp_status_one, DROP spn_one, DROP fmi_one, DROP occurrence_count_one, DROP malfunction_indicator_lamp_status_two, DROP red_stop_lamp_status_two, DROP amber_warning_lamp_status_two, DROP protect_lamp_status_two, DROP spn_two, DROP fmi_two, DROP occurrence_count_two');
        $this->addSql('ALTER TABLE line CHANGE direction direction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE week_interval week_interval VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
