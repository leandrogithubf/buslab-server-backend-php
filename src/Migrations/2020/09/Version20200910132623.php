<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200910132623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inserting new event categories';
    }

    public function up(Schema $schema): void
    {
        // Primeiro removendo caso tenha sido inserido previamente e depois inserindo
        $this->addSql('DELETE FROM `event_category` WHERE id BETWEEN 4 AND 17');
        $this->addSql('INSERT INTO `event_category` (id, description, identifier) VALUES
            ("4", "Atrasado na saída", "' . Identifier::database() . '"),
            ("5", "Atrasado na chegada", "' . Identifier::database() . '"),
            ("6", "Rota inesperada", "' . Identifier::database() . '"),
            ("7", "Viagem não realizada", "' . Identifier::database() . '"),
            ("8", "Comboio", "' . Identifier::database() . '"),
            ("9", "Obd removido", "' . Identifier::database() . '"),
            ("10", "Ociosidade", "' . Identifier::database() . '"),
            ("11", "Faixa verde", "' . Identifier::database() . '"),
            ("12", "Faixa vermelha", "' . Identifier::database() . '"),
            ("13", "Sobreaquecimento do motor", "' . Identifier::database() . '"),
            ("14", "Falha no motor", "' . Identifier::database() . '"),
            ("15", "Atrasado na saída", "' . Identifier::database() . '"),
            ("16", "Atrasado na chegada", "' . Identifier::database() . '"),
            ("17", "Direção perigosa", "' . Identifier::database() . '")
        ;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `event_category` WHERE id BETWEEN 4 AND 17');
    }
}
