<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200618161121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE parameter_value (id INT AUTO_INCREMENT NOT NULL, obd_id INT NOT NULL, parameter_id INT NOT NULL, value VARCHAR(255) NOT NULL, identifier VARCHAR(15) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6DB2A2B886D64477 (obd_id), INDEX IDX_6DB2A2B87C56DBD6 (parameter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parameter_value ADD CONSTRAINT FK_6DB2A2B886D64477 FOREIGN KEY (obd_id) REFERENCES obd (id)');
        $this->addSql('ALTER TABLE parameter_value ADD CONSTRAINT FK_6DB2A2B87C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameter (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE parameter_value');
    }
}
