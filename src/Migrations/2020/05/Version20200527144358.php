<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200527144358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vehicle ADD description VARCHAR(120) NOT NULL');

        $this->addSql('CREATE TABLE fuel_quote (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, value VARCHAR(25) NOT NULL, date DATETIME NOT NULL, INDEX IDX_1D1945E979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fuel_quote ADD CONSTRAINT FK_1D1945E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE fuel_quote ADD identifier VARCHAR(15) NOT NULL, ADD is_active TINYINT(1) NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vehicle DROP description');
        $this->addSql('DROP TABLE fuel_quote');
        $this->addSql('ALTER TABLE fuel_quote DROP identifier, DROP is_active, DROP deleted_at, DROP created_at, DROP updated_at');
    }
}
