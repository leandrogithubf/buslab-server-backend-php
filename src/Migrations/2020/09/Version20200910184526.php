<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200910184526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixing the line and line_points relation ';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE line_point CHANGE line_id line_id INT NOT NULL');
        $this->addSql('ALTER TABLE line CHANGE code code VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE line CHANGE code code INT NOT NULL');
        $this->addSql('ALTER TABLE line_point CHANGE line_id line_id INT DEFAULT NULL');
    }
}
