<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200528172430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE company_place (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, city_id INT NOT NULL, street_code VARCHAR(15) NOT NULL, street_name VARCHAR(250) NOT NULL, street_number VARCHAR(10) NOT NULL, street_complement VARCHAR(50) DEFAULT NULL, street_district VARCHAR(50) DEFAULT NULL, identifier VARCHAR(15) NOT NULL, description VARCHAR(120) NOT NULL, is_active TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_33ECF1A8979B1AD6 (company_id), INDEX IDX_33ECF1A88BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_place ADD CONSTRAINT FK_33ECF1A8979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE company_place ADD CONSTRAINT FK_33ECF1A88BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE vehicle ADD model_id INT NOT NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4867975B7E7 FOREIGN KEY (model_id) REFERENCES vehicle_model (id)');
        $this->addSql('CREATE INDEX IDX_1B80E4867975B7E7 ON vehicle (model_id)');

        $this->addSql('CREATE TABLE event_status (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, identifier VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO `event_status` VALUES
            ("1", "Ignorado", "' . Identifier::database() . '"),
            ("2", "Visualisado", "' . Identifier::database() . '"),
            ("3", "Resolvido", "' . Identifier::database() . '"),
            ("4", "Em aberto", "' . Identifier::database() . '")
        ;');

        $this->addSql('CREATE TABLE event_category (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, identifier VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO `event_category` VALUES
            ("1", "Excesso de velocidade ", "' . Identifier::database() . '"),
            ("2", "Freada brusca", "' . Identifier::database() . '"),
            ("3", "Arrancada brusca", "' . Identifier::database() . '")
        ;');
        $this->addSql('CREATE TABLE event_modality (id INT AUTO_INCREMENT NOT NULL, modality VARCHAR(255) NOT NULL, identifier VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO `event_modality` VALUES
            ("1", "Ocorrência", "' . Identifier::database() . '"),
            ("2", "Evento", "' . Identifier::database() . '")
        ;');

        $this->addSql('CREATE TABLE trip_status (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, identifier VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO `trip_status` VALUES
            ("1", "Não realizada", "' . Identifier::database() . '"),
            ("2", "Realizada", "' . Identifier::database() . '"),
            ("3", "Programada", "' . Identifier::database() . '")
        ;');

        $this->addSql('CREATE TABLE trip_modality (id INT AUTO_INCREMENT NOT NULL, modality VARCHAR(255) NOT NULL, identifier VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO `trip_modality` VALUES
            ("1", "Programada", "' . Identifier::database() . '"),
            ("2", "Não programada", "' . Identifier::database() . '")
        ;');

        $this->addSql('CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, modality_id INT NOT NULL, name VARCHAR(255) NOT NULL, code INT NOT NULL, identifier VARCHAR(15) NOT NULL, is_active TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5D9F75A1979B1AD6 (company_id), INDEX IDX_5D9F75A12D6D889B (modality_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A12D6D889B FOREIGN KEY (modality_id) REFERENCES employee_modality (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE company_place');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E4867975B7E7');
        $this->addSql('DROP INDEX IDX_1B80E4867975B7E7 ON vehicle');
        $this->addSql('ALTER TABLE vehicle DROP model_id');

        $this->addSql('DROP TABLE event_status');
        $this->addSql('DROP TABLE event_category');
        $this->addSql('DROP TABLE event_modality');
        $this->addSql('DROP TABLE trip_status');
        $this->addSql('DROP TABLE trip_modality');
        $this->addSql('DROP TABLE employee');
    }
}
