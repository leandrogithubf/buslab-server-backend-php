<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200810022435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes incorrect company fields';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE company DROP maximum_speed_off_the_itinerary, DROP maximum_allowed_speed_in_line, DROP vehicle_operating_years_limit');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE company ADD maximum_speed_off_the_itinerary DOUBLE PRECISION NOT NULL, ADD maximum_allowed_speed_in_line DOUBLE PRECISION NOT NULL, ADD vehicle_operating_years_limit INT NOT NULL');
    }
}
