<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210802151812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 8');
        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 6');
        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 22');
        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 23');
        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 7');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 8');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 6');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 22');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 23');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 7');
    }
}
