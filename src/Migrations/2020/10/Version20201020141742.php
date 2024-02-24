<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201020141742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (18, "Assalto", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (19, "Acidente com vitima", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (20, "Acidente sem vítima", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (21, "Falta de operador", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (22, "Perda de viagem", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (23, "Desvio de itinerário", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (24, "Reparo", "' . Identifier::database() . '",null,2);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (25, "Socorro", "' . Identifier::database() . '",null,2);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (26, "Recolhida Anormal", "' . Identifier::database() . '",null, 2);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (27, "Troca de veículo", "' . Identifier::database() . '",null, 2);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (28, "Validador com defeito", "' . Identifier::database() . '",null,2);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (29, "Erro de programação", "' . Identifier::database() . '",null,4);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (30, "Congestionamento", "' . Identifier::database() . '",null,4);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (31, "Tempo de ciclo insuficiente", "' . Identifier::database() . '",null,4);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (32, "Validador com defeito", "' . Identifier::database() . '",null,3);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (33, "Sistema inoperante", "' . Identifier::database() . '",null,3);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (34, "Queda de árvore", "' . Identifier::database() . '",null,6);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (35, "obras na via", "' . Identifier::database() . '",null,6);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (36, "manifestações", "' . Identifier::database() . '",null,6);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (37, "Superaquecimento do motor", "' . Identifier::database() . '",null,2);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (38, "RPM acima do padrão", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (39, "Embreagem acionada por mais de 20 seg.", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (40, "Partir em 2 marcha", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (41, "Banguela", "' . Identifier::database() . '",null,1);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (42, "Shut - Down", "' . Identifier::database() . '",null,1);');
        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 1');
        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 2');
        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 3');
        $this->addSql('UPDATE event_category SET sector_id = 1 WHERE id = 8');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DELETE FROM event_category WHERE id = 3');
        $this->addSql('DELETE FROM event_category WHERE id = 18');
        $this->addSql('DELETE FROM event_category WHERE id = 19');
        $this->addSql('DELETE FROM event_category WHERE id = 20');
        $this->addSql('DELETE FROM event_category WHERE id = 21');
        $this->addSql('DELETE FROM event_category WHERE id = 22');
        $this->addSql('DELETE FROM event_category WHERE id = 23');
        $this->addSql('DELETE FROM event_category WHERE id = 24');
        $this->addSql('DELETE FROM event_category WHERE id = 25');
        $this->addSql('DELETE FROM event_category WHERE id = 26');
        $this->addSql('DELETE FROM event_category WHERE id = 27');
        $this->addSql('DELETE FROM event_category WHERE id = 28');
        $this->addSql('DELETE FROM event_category WHERE id = 29');
        $this->addSql('DELETE FROM event_category WHERE id = 30');
        $this->addSql('DELETE FROM event_category WHERE id = 31');
        $this->addSql('DELETE FROM event_category WHERE id = 32');
        $this->addSql('DELETE FROM event_category WHERE id = 33');
        $this->addSql('DELETE FROM event_category WHERE id = 34');
        $this->addSql('DELETE FROM event_category WHERE id = 35');
        $this->addSql('DELETE FROM event_category WHERE id = 36');
        $this->addSql('DELETE FROM event_category WHERE id = 37');
        $this->addSql('DELETE FROM event_category WHERE id = 38');
        $this->addSql('DELETE FROM event_category WHERE id = 38');
        $this->addSql('DELETE FROM event_category WHERE id = 39');
        $this->addSql('DELETE FROM event_category WHERE id = 40');
        $this->addSql('DELETE FROM event_category WHERE id = 41');
        $this->addSql('DELETE FROM event_category WHERE id = 42');

        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 1');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 2');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 3');
        $this->addSql('UPDATE event_category SET sector_id = null WHERE id = 8');
    }
}
