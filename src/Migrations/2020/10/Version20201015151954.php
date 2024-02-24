<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201015151954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_category ADD sector_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_category ADD CONSTRAINT FK_40A0F011DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
        $this->addSql('CREATE INDEX IDX_40A0F011DE95C867 ON event_category (sector_id)');
        $this->addSql('ALTER TABLE line CHANGE direction direction ENUM(\'GOING\', \'RETURN\', \'CIRCULATE\')');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality ENUM(\'TRIP\', \'MOVEMENT\', \'RESERVED\'), CHANGE week_interval week_interval ENUM(\'WEEKDAY\', \'SATURDAY\', \'SUNDAY\')');
        $this->addSql('INSERT INTO `sector` (id, description, identifier) VALUES
            (1, "Operacao", "' . Identifier::database() . '"),
            (2, "Manutencao", "' . Identifier::database() . '"),
            (3, "Bilhetagem", "' . Identifier::database() . '"),
            (4, "Planejamento", "' . Identifier::database() . '"),
            (5, "Telemetria", "' . Identifier::database() . '")
        ;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_category DROP FOREIGN KEY FK_40A0F011DE95C867');
        $this->addSql('DROP INDEX IDX_40A0F011DE95C867 ON event_category');
        $this->addSql('ALTER TABLE event_category DROP sector_id');
        $this->addSql('ALTER TABLE line CHANGE direction direction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule CHANGE modality modality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE week_interval week_interval VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DELETE FROM `sector` WHERE `id` = 1');
        $this->addSql('DELETE FROM `sector` WHERE `id` = 2');
        $this->addSql('DELETE FROM `sector` WHERE `id` = 3');
        $this->addSql('DELETE FROM `sector` WHERE `id` = 4');
        $this->addSql('DELETE FROM `sector` WHERE `id` = 5');
    }
}
