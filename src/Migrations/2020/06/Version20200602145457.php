<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200602145457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE checkpoint (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, obd_id INT NOT NULL, date DATETIME NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, distance DOUBLE PRECISION NOT NULL, angle DOUBLE PRECISION NOT NULL, hdop DOUBLE PRECISION NOT NULL, rpm DOUBLE PRECISION NOT NULL, fuel DOUBLE PRECISION NOT NULL, speed DOUBLE PRECISION NOT NULL, map DOUBLE PRECISION NOT NULL, ect DOUBLE PRECISION NOT NULL, iat DOUBLE PRECISION NOT NULL, errors VARCHAR(255) NOT NULL, alerts VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, description VARCHAR(120) NOT NULL, identifier VARCHAR(15) NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_F00F7BE545317D1 (vehicle_id), INDEX IDX_F00F7BE86D64477 (obd_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE checkpoint ADD CONSTRAINT FK_F00F7BE545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE checkpoint ADD CONSTRAINT FK_F00F7BE86D64477 FOREIGN KEY (obd_id) REFERENCES obd (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE checkpoint');
    }
}
