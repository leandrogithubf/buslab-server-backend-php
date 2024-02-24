<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200522182527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create states table';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE state (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(2) NOT NULL, initials VARCHAR(2) NOT NULL, name VARCHAR(20) NOT NULL, identifier VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO `state` VALUES
            ("1", "11", "RO", "Rondônia", "' . Identifier::database() . '"),
            ("2", "12", "AC", "Acre", "' . Identifier::database() . '"),
            ("3", "13", "AM", "Amazonas", "' . Identifier::database() . '"),
            ("4", "14", "RR", "Roraima", "' . Identifier::database() . '"),
            ("5", "15", "PA", "Pará", "' . Identifier::database() . '"),
            ("6", "16", "AP", "Amapá", "' . Identifier::database() . '"),
            ("7", "17", "TO", "Tocantins", "' . Identifier::database() . '"),
            ("8", "21", "MA", "Maranhão", "' . Identifier::database() . '"),
            ("9", "22", "PI", "Piauí", "' . Identifier::database() . '"),
            ("10", "23", "CE", "Ceará", "' . Identifier::database() . '"),
            ("11", "24", "RN", "Rio Grande do Norte", "' . Identifier::database() . '"),
            ("12", "25", "PB", "Paraíba", "' . Identifier::database() . '"),
            ("13", "26", "PE", "Pernambuco", "' . Identifier::database() . '"),
            ("14", "27", "AL", "Alagoas", "' . Identifier::database() . '"),
            ("15", "28", "SE", "Sergipe", "' . Identifier::database() . '"),
            ("16", "29", "BA", "Bahia", "' . Identifier::database() . '"),
            ("17", "31", "MG", "Minas Gerais", "' . Identifier::database() . '"),
            ("18", "32", "ES", "Espírito Santo", "' . Identifier::database() . '"),
            ("19", "33", "RJ", "Rio de Janeiro", "' . Identifier::database() . '"),
            ("20", "35", "SP", "São Paulo", "' . Identifier::database() . '"),
            ("21", "41", "PR", "Paraná", "' . Identifier::database() . '"),
            ("22", "42", "SC", "Santa Catarina", "' . Identifier::database() . '"),
            ("23", "43", "RS", "Rio Grande do Sul", "' . Identifier::database() . '"),
            ("24", "50", "MS", "Mato Grosso do Sul", "' . Identifier::database() . '"),
            ("25", "51", "MT", "Mato Grosso", "' . Identifier::database() . '"),
            ("26", "52", "GO", "Goiás", "' . Identifier::database() . '"),
            ("27", "53", "DF", "Distrito Federal", "' . Identifier::database() . '")
        ;');

        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, state_id INT NOT NULL, code VARCHAR(7) DEFAULT NULL, name VARCHAR(250) NOT NULL, identifier VARCHAR(15) NOT NULL, INDEX IDX_2D5B02345D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02345D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM state;');
        $this->addSql('DROP TABLE state');

        $this->addSql('DROP TABLE city');
    }
}
