<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201004182513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint ADD trip_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE checkpoint ADD CONSTRAINT FK_F00F7BEA5BC2E0E FOREIGN KEY (trip_id) REFERENCES trip (id)');
        $this->addSql('CREATE INDEX IDX_F00F7BEA5BC2E0E ON checkpoint (trip_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint DROP FOREIGN KEY FK_F00F7BEA5BC2E0E');
        $this->addSql('DROP INDEX IDX_F00F7BEA5BC2E0E ON checkpoint');
        $this->addSql('ALTER TABLE checkpoint DROP trip_id');
    }
}
