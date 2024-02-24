<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200608144529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E4862CFDEE0B');
        $this->addSql('DROP INDEX UNIQ_1B80E4862CFDEE0B ON vehicle');
        $this->addSql('ALTER TABLE vehicle DROP odb_id');
        $this->addSql('ALTER TABLE obd ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE obd ADD CONSTRAINT FK_A1DEF96B979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_A1DEF96B979B1AD6 ON obd (company_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE obd DROP FOREIGN KEY FK_A1DEF96B979B1AD6');
        $this->addSql('DROP INDEX IDX_A1DEF96B979B1AD6 ON obd');
        $this->addSql('ALTER TABLE obd DROP company_id');
        $this->addSql('ALTER TABLE vehicle ADD odb_id INT NOT NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4862CFDEE0B FOREIGN KEY (odb_id) REFERENCES obd (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1B80E4862CFDEE0B ON vehicle (odb_id)');
    }
}
