<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200805204430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allowin null values for checkpoints data';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint CHANGE latitude latitude DOUBLE PRECISION DEFAULT NULL, CHANGE longitude longitude DOUBLE PRECISION DEFAULT NULL, CHANGE distance distance DOUBLE PRECISION DEFAULT NULL, CHANGE angle angle DOUBLE PRECISION DEFAULT NULL, CHANGE hdop hdop DOUBLE PRECISION DEFAULT NULL, CHANGE rpm rpm DOUBLE PRECISION DEFAULT NULL, CHANGE fuel fuel DOUBLE PRECISION DEFAULT NULL, CHANGE speed speed DOUBLE PRECISION DEFAULT NULL, CHANGE map map DOUBLE PRECISION DEFAULT NULL, CHANGE ect ect DOUBLE PRECISION DEFAULT NULL, CHANGE iat iat DOUBLE PRECISION DEFAULT NULL, CHANGE errors errors VARCHAR(255) DEFAULT NULL, CHANGE alerts alerts VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint CHANGE latitude latitude DOUBLE PRECISION NOT NULL, CHANGE longitude longitude DOUBLE PRECISION NOT NULL, CHANGE distance distance DOUBLE PRECISION NOT NULL, CHANGE angle angle DOUBLE PRECISION NOT NULL, CHANGE hdop hdop DOUBLE PRECISION NOT NULL, CHANGE rpm rpm DOUBLE PRECISION NOT NULL, CHANGE fuel fuel DOUBLE PRECISION NOT NULL, CHANGE speed speed DOUBLE PRECISION NOT NULL, CHANGE map map DOUBLE PRECISION NOT NULL, CHANGE ect ect DOUBLE PRECISION NOT NULL, CHANGE iat iat DOUBLE PRECISION NOT NULL, CHANGE errors errors VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE alerts alerts VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
