<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200809200017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds FKs to event_category to handle parameter configuration and renames fields descriptions fields form event extra tables';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_category ADD parameter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_category ADD CONSTRAINT FK_40A0F0117C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameter (id)');
        $this->addSql('CREATE INDEX IDX_40A0F0117C56DBD6 ON event_category (parameter_id)');
        $this->addSql('ALTER TABLE parameter_configuration ADD event_category_id INT NOT NULL');
        $this->addSql('ALTER TABLE parameter_configuration ADD CONSTRAINT FK_D98F3F1DB9CF4E62 FOREIGN KEY (event_category_id) REFERENCES event_category (id)');
        $this->addSql('CREATE INDEX IDX_D98F3F1DB9CF4E62 ON parameter_configuration (event_category_id)');
        $this->addSql('ALTER TABLE parameter ADD description VARCHAR(120) NOT NULL, DROP parameter');

        $this->addSql('ALTER TABLE event_modality ADD description VARCHAR(120) NOT NULL');
        $this->addSql('UPDATE event_modality set description = modality');
        $this->addSql('ALTER TABLE event_modality DROP modality');

        $this->addSql('ALTER TABLE event_status ADD description VARCHAR(120) NOT NULL');
        $this->addSql('UPDATE event_status set description = `status`');
        $this->addSql('ALTER TABLE event_status DROP `status`');

        $this->addSql('ALTER TABLE event_category CHANGE description description VARCHAR(120) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_category DROP FOREIGN KEY FK_40A0F0117C56DBD6');
        $this->addSql('DROP INDEX IDX_40A0F0117C56DBD6 ON event_category');
        $this->addSql('ALTER TABLE event_category DROP parameter_id');
        $this->addSql('ALTER TABLE parameter ADD parameter VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP description');
        $this->addSql('ALTER TABLE parameter_configuration DROP FOREIGN KEY FK_D98F3F1DB9CF4E62');
        $this->addSql('DROP INDEX IDX_D98F3F1DB9CF4E62 ON parameter_configuration');
        $this->addSql('ALTER TABLE parameter_configuration DROP event_category_id');

        $this->addSql('ALTER TABLE event_category CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE event_modality ADD modality VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP description');
        $this->addSql('ALTER TABLE event_status ADD status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP description');

        $this->addSql('ALTER TABLE event_category CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
