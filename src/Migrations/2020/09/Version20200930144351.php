<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200930144351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE schedule_date (id INT AUTO_INCREMENT NOT NULL, driver_id INT NOT NULL, collector_id INT NOT NULL, date DATE NOT NULL, identifier VARCHAR(15) NOT NULL, INDEX IDX_4AD61721C3423909 (driver_id), INDEX IDX_4AD61721670BAFFE (collector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE schedule_date ADD CONSTRAINT FK_4AD61721C3423909 FOREIGN KEY (driver_id) REFERENCES employee (id)');
        $this->addSql('ALTER TABLE schedule_date ADD CONSTRAINT FK_4AD61721670BAFFE FOREIGN KEY (collector_id) REFERENCES employee (id)');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB493E84BE');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB8C03F15C');
        $this->addSql('DROP INDEX IDX_5A3811FB493E84BE ON schedule');
        $this->addSql('DROP INDEX IDX_5A3811FB8C03F15C ON schedule');
        $this->addSql('ALTER TABLE schedule ADD company_id INT DEFAULT NULL, ADD driver_id INT DEFAULT NULL, ADD collector_id INT DEFAULT NULL, ADD modality ENUM(\'TRIP\', \'MOVEMENT\', \'RESERVED\'), ADD `interval` ENUM(\'WEEKDAY\', \'SATURDAY\', \'SUNDAY\'), ADD starts_at TIME DEFAULT NULL, ADD ends_at TIME DEFAULT NULL, DROP employee_id, DROP employee_collector_id, DROP hour_term_going, DROP hour_term_arrival, DROP hor_out_collected, DROP sequenc_prog, DROP service, CHANGE description description VARCHAR(120) NOT NULL');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBC3423909 FOREIGN KEY (driver_id) REFERENCES employee (id)');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB670BAFFE FOREIGN KEY (collector_id) REFERENCES employee (id)');
        $this->addSql('CREATE INDEX IDX_5A3811FB979B1AD6 ON schedule (company_id)');
        $this->addSql('CREATE INDEX IDX_5A3811FBC3423909 ON schedule (driver_id)');
        $this->addSql('CREATE INDEX IDX_5A3811FB670BAFFE ON schedule (collector_id)');
        $this->addSql('ALTER TABLE line CHANGE direction direction ENUM(\'GOING\', \'RETURN\', \'CIRCULATE\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE schedule_date');
        $this->addSql('ALTER TABLE line CHANGE direction direction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB979B1AD6');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBC3423909');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB670BAFFE');
        $this->addSql('DROP INDEX IDX_5A3811FB979B1AD6 ON schedule');
        $this->addSql('DROP INDEX IDX_5A3811FBC3423909 ON schedule');
        $this->addSql('DROP INDEX IDX_5A3811FB670BAFFE ON schedule');
        $this->addSql('ALTER TABLE schedule ADD employee_id INT DEFAULT NULL, ADD employee_collector_id INT DEFAULT NULL, ADD hour_term_going TIME DEFAULT NULL, ADD hour_term_arrival TIME DEFAULT NULL, ADD hor_out_collected TIME DEFAULT NULL, ADD sequenc_prog INT DEFAULT NULL, ADD service VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP company_id, DROP driver_id, DROP collector_id, DROP modality, DROP `interval`, DROP starts_at, DROP ends_at, CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB493E84BE FOREIGN KEY (employee_collector_id) REFERENCES employee (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5A3811FB493E84BE ON schedule (employee_collector_id)');
        $this->addSql('CREATE INDEX IDX_5A3811FB8C03F15C ON schedule (employee_id)');
    }
}
