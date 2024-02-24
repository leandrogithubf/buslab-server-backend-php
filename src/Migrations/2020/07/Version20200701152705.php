<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701152705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trip ADD status_id INT NOT NULL, ADD modality_id INT NOT NULL');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B6BF700BD FOREIGN KEY (status_id) REFERENCES trip_status (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B2D6D889B FOREIGN KEY (modality_id) REFERENCES trip_modality (id)');
        $this->addSql('CREATE INDEX IDX_7656F53B6BF700BD ON trip (status_id)');
        $this->addSql('CREATE INDEX IDX_7656F53B2D6D889B ON trip (modality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53B6BF700BD');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53B2D6D889B');
        $this->addSql('DROP INDEX IDX_7656F53B6BF700BD ON trip');
        $this->addSql('DROP INDEX IDX_7656F53B2D6D889B ON trip');
        $this->addSql('ALTER TABLE trip DROP status_id, DROP modality_id');
    }
}
