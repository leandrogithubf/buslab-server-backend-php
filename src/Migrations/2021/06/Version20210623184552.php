<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210623184552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint ADD compass_bearing INT DEFAULT NULL, ADD navigation_vehicle_speed DOUBLE PRECISION DEFAULT NULL, ADD pitch INT DEFAULT NULL, ADD altitude INT DEFAULT NULL, ADD engine_trip_fuel INT DEFAULT NULL, ADD engine_fuel_used INT DEFAULT NULL, ADD acc_pedal_position INT DEFAULT NULL, ADD fuel_delivery_pressure INT DEFAULT NULL, ADD eng_extended_crankcase_pressure INT DEFAULT NULL, ADD engine_oil_level INT DEFAULT NULL, ADD engine_oil_pressure INT DEFAULT NULL, ADD engine_crankcase_pressure INT DEFAULT NULL, ADD engine_coolant_pressure INT DEFAULT NULL, ADD engine_coolant_level INT DEFAULT NULL, ADD two_speed_axle_switch INT DEFAULT NULL, ADD parking_brake_switch INT DEFAULT NULL, ADD cruise_control_pause_switch INT DEFAULT NULL, ADD park_brake_release INT DEFAULT NULL, ADD wheel_vehicle_speed DOUBLE PRECISION DEFAULT NULL, ADD cruise_control_active INT DEFAULT NULL, ADD cruise_control_enable_switch INT DEFAULT NULL, ADD brake_switch INT DEFAULT NULL, ADD clutch_switch INT DEFAULT NULL, ADD cruise_control_set_switch INT DEFAULT NULL, ADD cruise_control_coast_switch INT DEFAULT NULL, ADD cruise_control_resume_switch INT DEFAULT NULL, ADD cruise_control_accelerate_switch INT DEFAULT NULL, ADD cruise_control_set_speed DOUBLE PRECISION DEFAULT NULL, ADD pto_governor_state INT DEFAULT NULL, ADD cruise_control_states INT DEFAULT NULL, ADD engine_idle_increment_switch INT DEFAULT NULL, ADD engine_idle_decrement_switch INT DEFAULT NULL, ADD engine_test_mode_switch INT DEFAULT NULL, ADD engine_shutdown_override_switch INT DEFAULT NULL, ADD engine_diesel_particulate_filter INT DEFAULT NULL, ADD engine_intake_manifold_pressure INT DEFAULT NULL, ADD engine_intake_manifold_temperature INT DEFAULT NULL, ADD engine_air_inlet_pressure INT DEFAULT NULL, ADD engine_air_filter_differential_pressure INT DEFAULT NULL, ADD engine_exhaust_gas_temperature INT DEFAULT NULL, ADD engine_coolant_filter_differential INT DEFAULT NULL, ADD net_battery_current INT DEFAULT NULL, ADD alternator_current INT DEFAULT NULL, ADD charging_systempotential INT DEFAULT NULL, ADD battery_potential_input INT DEFAULT NULL, ADD keyswitch_battery_potential INT DEFAULT NULL, ADD washer_fluid_level INT DEFAULT NULL, ADD fuel_level_one INT DEFAULT NULL, ADD engine_fuel_filter_pressure INT DEFAULT NULL, ADD engine_oil_filter_pressure INT DEFAULT NULL, ADD cargo_ambient_temperature INT DEFAULT NULL, ADD fuel_level_two INT DEFAULT NULL, ADD dmc_one DOUBLE PRECISION DEFAULT NULL, ADD dmc_two DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE line CHANGE direction direction ENUM(\'GOING\', \'RETURN\', \'CIRCULATE\')');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality ENUM(\'TRIP\', \'MOVEMENT\', \'RESERVED\', \'STARTING_OPERATION\', \'CLOSING_OPERATION\'), CHANGE week_interval week_interval ENUM(\'WEEKDAY\', \'SATURDAY\', \'SUNDAY\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint DROP compass_bearing, DROP navigation_vehicle_speed, DROP pitch, DROP altitude, DROP engine_trip_fuel, DROP engine_fuel_used, DROP acc_pedal_position, DROP fuel_delivery_pressure, DROP eng_extended_crankcase_pressure, DROP engine_oil_level, DROP engine_oil_pressure, DROP engine_crankcase_pressure, DROP engine_coolant_pressure, DROP engine_coolant_level, DROP two_speed_axle_switch, DROP parking_brake_switch, DROP cruise_control_pause_switch, DROP park_brake_release, DROP wheel_vehicle_speed, DROP cruise_control_active, DROP cruise_control_enable_switch, DROP brake_switch, DROP clutch_switch, DROP cruise_control_set_switch, DROP cruise_control_coast_switch, DROP cruise_control_resume_switch, DROP cruise_control_accelerate_switch, DROP cruise_control_set_speed, DROP pto_governor_state, DROP cruise_control_states, DROP engine_idle_increment_switch, DROP engine_idle_decrement_switch, DROP engine_test_mode_switch, DROP engine_shutdown_override_switch, DROP engine_diesel_particulate_filter, DROP engine_intake_manifold_pressure, DROP engine_intake_manifold_temperature, DROP engine_air_inlet_pressure, DROP engine_air_filter_differential_pressure, DROP engine_exhaust_gas_temperature, DROP engine_coolant_filter_differential, DROP net_battery_current, DROP alternator_current, DROP charging_systempotential, DROP battery_potential_input, DROP keyswitch_battery_potential, DROP washer_fluid_level, DROP fuel_level_one, DROP engine_fuel_filter_pressure, DROP engine_oil_filter_pressure, DROP cargo_ambient_temperature, DROP fuel_level_two, DROP dmc_one, DROP dmc_two');
        $this->addSql('ALTER TABLE line CHANGE direction direction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE week_interval week_interval VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
