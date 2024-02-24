<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200630185644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE trip (id INT AUTO_INCREMENT NOT NULL, driver_id INT NOT NULL, collector_id INT DEFAULT NULL, line_id INT NOT NULL, vehicle_id INT NOT NULL, obd_id INT NOT NULL, company_id INT NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME DEFAULT NULL, identifier VARCHAR(15) NOT NULL, is_active TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7656F53BC3423909 (driver_id), INDEX IDX_7656F53B670BAFFE (collector_id), INDEX IDX_7656F53B4D7B7542 (line_id), INDEX IDX_7656F53B545317D1 (vehicle_id), INDEX IDX_7656F53B86D64477 (obd_id), INDEX IDX_7656F53B979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BC3423909 FOREIGN KEY (driver_id) REFERENCES employee (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B670BAFFE FOREIGN KEY (collector_id) REFERENCES employee (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B4D7B7542 FOREIGN KEY (line_id) REFERENCES line (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B86D64477 FOREIGN KEY (obd_id) REFERENCES obd (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE trip');
    }
}
