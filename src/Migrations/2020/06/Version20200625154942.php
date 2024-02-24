<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200625154942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE schedule (id INT AUTO_INCREMENT NOT NULL, table_code VARCHAR(25) DEFAULT NULL, term_going VARCHAR(100) DEFAULT NULL, term_back VARCHAR(100) DEFAULT NULL, sequence INT DEFAULT NULL, is_productive TINYINT(1) NOT NULL, direction VARCHAR(10) NOT NULL, hour_term_going TIME NOT NULL, hour_term_back TIME NOT NULL, hor_out_collected TIME DEFAULT NULL, sequenc_prog INT DEFAULT NULL, data_validity DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE schedule ADD identifier VARCHAR(15) NOT NULL, ADD is_active TINYINT(1) NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE schedule DROP identifier, DROP is_active, DROP deleted_at, DROP created_at, DROP updated_at');
    }
}
