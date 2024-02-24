<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200910235148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reinserting all the system parameters for companies';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM `parameter`;');
        $this->addSql('INSERT INTO `parameter` (id, description, identifier) VALUES
            (1, "Velocidade", "' . Identifier::database() . '"),
            (2, "Rotação do Motor (RPM)", "' . Identifier::database() . '"),
            (3, "Idade do veículo", "' . Identifier::database() . '")
        ;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `parameter`;');
    }
}
