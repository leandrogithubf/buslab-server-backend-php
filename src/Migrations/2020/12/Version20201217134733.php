<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201217134733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('UPDATE event_category SET description="Acidente com vítima" WHERE id=19');
        $this->addSql('UPDATE event_category SET description="Obras na via" WHERE id = 35');
        $this->addSql('UPDATE event_category SET description="Manifestações" WHERE id = 36');
        $this->addSql('UPDATE event_category SET description="Veículo desengatado em movimento (Banguela)" WHERE id = 41');
        $this->addSql('UPDATE event_category SET description="Shut-down" WHERE id = 42');
        $this->addSql('DELETE FROM event_category WHERE id = 28;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
