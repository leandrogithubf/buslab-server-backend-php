<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201020141854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM event_category WHERE id = 10');
        $this->addSql('DELETE FROM event_category WHERE id = 11');
        $this->addSql('DELETE FROM event_category WHERE id = 12');
        $this->addSql('DELETE FROM event_category WHERE id = 13');
        $this->addSql('DELETE FROM event_category WHERE id = 17');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 4');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 5');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 6');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 7');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 9');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 14');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 15');
        $this->addSql('UPDATE event_category SET sector_id = 5 WHERE id = 16');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `event_category` (id, description, identifier) VALUES
            ("10", "Ociosidade", "' . Identifier::database() . '"),
            ("11", "Faixa verde", "' . Identifier::database() . '"),
            ("12", "Faixa vermelha", "' . Identifier::database() . '"),
            ("13", "Sobreaquecimento do motor", "' . Identifier::database() . '"),
            ("17", "Direção perigosa", "' . Identifier::database() . '"),
            ;');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 4');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 5');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 6');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 7');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 9');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 14');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 15');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 16');
    }
}
