<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210316130352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vehicle_model ADD ect DOUBLE PRECISION DEFAULT NULL, ADD iat DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('UPDATE vehicle_model SET ect = 95.0 WHERE ect is null');
        $this->addSql('UPDATE vehicle_model SET iat = 99.0 WHERE iat is null');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vehicle_model DROP ect, DROP iat');

        $this->addSql('UPDATE vehicle_model SET ect = null WHERE ect is not null');
        $this->addSql('UPDATE vehicle_model SET iat = null WHERE iat is not null');
    }
}
