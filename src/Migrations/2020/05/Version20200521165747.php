<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200521165747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cellphone_number (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(11) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE cellphone_number ADD identifier VARCHAR(15) NOT NULL, ADD description VARCHAR(120) NOT NULL, ADD is_active TINYINT(1) NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');

        $this->addSql('ALTER TABLE cellphone_number DROP description');

        $this->addSql('CREATE TABLE obd (id INT AUTO_INCREMENT NOT NULL, cellphone_number_id INT DEFAULT NULL, serial VARCHAR(15) NOT NULL, version VARCHAR(255) NOT NULL, INDEX IDX_A1DEF96B8771CB92 (cellphone_number_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE obd ADD CONSTRAINT FK_A1DEF96B8771CB92 FOREIGN KEY (cellphone_number_id) REFERENCES cellphone_number (id)');

        $this->addSql('ALTER TABLE obd ADD identifier VARCHAR(15) NOT NULL, ADD is_active TINYINT(1) NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');

        $this->addSql('CREATE TABLE employee_modality (id INT AUTO_INCREMENT NOT NULL, identifier VARCHAR(15) NOT NULL, description VARCHAR(60) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO employee_modality (id, identifier, description) VALUES
            ("1", "' . Identifier::database() . '", "Motorista"),
            ("2", "' . Identifier::database() . '", "Cobrador")
        ;');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cellphone_number DROP identifier, DROP description, DROP is_active, DROP deleted_at, DROP created_at, DROP updated_at');

        $this->addSql('ALTER TABLE cellphone_number ADD description VARCHAR(120) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP TABLE cellphone_number');

        $this->addSql('ALTER TABLE obd DROP identifier, DROP is_active, DROP deleted_at, DROP created_at, DROP updated_at');
        $this->addSql('DROP TABLE obd');

        $this->addSql('DELETE FROM employee_modality WHERE id = 1;');
        $this->addSql('DELETE FROM employee_modality WHERE id = 2;');
        $this->addSql('DROP TABLE employee_modality');
    }
}
