<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200503185636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds fields to the user entity of this application';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD name VARCHAR(255) NOT NULL, ADD cellphone VARCHAR(11) DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD document_number VARCHAR(11) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP name, DROP cellphone, DROP email, DROP document_number');
    }
}
