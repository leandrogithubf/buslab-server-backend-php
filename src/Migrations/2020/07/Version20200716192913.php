<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200716192913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, type_id INT DEFAULT NULL, consumption NUMERIC(5, 2) DEFAULT NULL, consumption_real NUMERIC(5, 2) DEFAULT NULL, speed_average NUMERIC(5, 2) DEFAULT NULL, speed_max NUMERIC(5, 2) DEFAULT NULL, distance INT DEFAULT NULL, INDEX IDX_C42F7784C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784C54C8C93 FOREIGN KEY (type_id) REFERENCES report_type (id)');
        $this->addSql('ALTER TABLE trip ADD report_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id)');
        $this->addSql('CREATE INDEX IDX_7656F53B4BD2A4C0 ON trip (report_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53B4BD2A4C0');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP INDEX IDX_7656F53B4BD2A4C0 ON trip');
        $this->addSql('ALTER TABLE trip DROP report_id');
    }
}
