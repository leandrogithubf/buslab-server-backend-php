<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111153319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO parameter (id, identifier,description) VALUES
            ("18", "ajtUpcKR3h9HP5D","Tempo de validade de inspeção obrigatória")
        ;');
        $this->addSql('INSERT INTO event_category (id, description, identifier, parameter_id, sector_id) VALUES
            ("46", "Inspeção vencida", "ajtUpcKR3h9HP5D", null, 2)
        ;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM event_category WHERE id = 46;');
        $this->addSql('DELETE FROM parameter WHERE id = 18;');
    }
}
