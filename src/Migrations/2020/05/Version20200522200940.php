<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200522200940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create company table';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, street_code VARCHAR(15) NOT NULL, street_name VARCHAR(250) NOT NULL, street_number VARCHAR(10) NOT NULL, street_complement VARCHAR(50) DEFAULT NULL, street_district VARCHAR(50) DEFAULT NULL, identifier VARCHAR(15) NOT NULL, description VARCHAR(120) NOT NULL, is_active TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4FBF094F8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');

        $this->addSql('CREATE TABLE vehicle_brand (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(120) NOT NULL, is_active TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle_model (id INT AUTO_INCREMENT NOT NULL, fuel_density DOUBLE PRECISION DEFAULT NULL, air_fuel_ratio DOUBLE PRECISION DEFAULT NULL, efficiency DOUBLE PRECISION DEFAULT NULL, description VARCHAR(120) NOT NULL, is_active TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE vehicle_brand');
        $this->addSql('DROP TABLE vehicle_model');
    }
}
