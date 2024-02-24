<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200618133530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE parameter (id INT AUTO_INCREMENT NOT NULL, parameter VARCHAR(255) NOT NULL, identifier VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO `parameter` VALUES
            ("1", "Speed", "' . Identifier::database() . '"),
            ("2", "RPM", "' . Identifier::database() . '"),
            ("3", "Date", "' . Identifier::database() . '"),
            ("4", "Latitude", "' . Identifier::database() . '"),
            ("5", "Longitude", "' . Identifier::database() . '"),
            ("6", "Distance", "' . Identifier::database() . '"),
            ("7", "Angle", "' . Identifier::database() . '"),
            ("8", "Error_hdop", "' . Identifier::database() . '"),
            ("9", "Fuel", "' . Identifier::database() . '"),
            ("10", "MAP", "' . Identifier::database() . '"),
            ("11", "ECT", "' . Identifier::database() . '"),
            ("12", "IAT", "' . Identifier::database() . '"),
            ("13", "ERRORS", "' . Identifier::database() . '"),
            ("14", "Alerts", "' . Identifier::database() . '")
        ;');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE parameter');
    }
}
