<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200623211234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE line ADD `grouping` VARCHAR(255) NOT NULL, ADD sense VARCHAR(255) NOT NULL, ADD point INT NOT NULL');
        $this->addSql('ALTER TABLE line RENAME INDEX idx_2c42079979b1ad6 TO IDX_D114B4F6979B1AD6');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE line DROP `grouping`, DROP sense, DROP point');
        $this->addSql('ALTER TABLE line RENAME INDEX idx_d114b4f6979b1ad6 TO IDX_2C42079979B1AD6');
    }
}
