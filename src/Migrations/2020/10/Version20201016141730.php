<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201016141730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE sector SET description= "Operação" WHERE id = 1');
        $this->addSql('UPDATE sector SET description= "Manutenção" WHERE id = 2');
        $this->addSql('INSERT INTO sector (id, description, identifier) VALUES
            (6, "Outros", "' . Identifier::database() . '")
        ;');
        $this->addSql('INSERT INTO event_modality (id, description, identifier) VALUES
            (3, "Outros", "' . Identifier::database() . '")
        ;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('UPDATE sector SET description = "Operacao" WHERE id = 1');
        $this->addSql('UPDATE sector SET description = "Manutencao" WHERE id = 2');
        $this->addSql('DELETE FROM sector WHERE id = 6');
        $this->addSql('DELETE FROM event_modality WHERE id = 3');
    }
}
