<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\TripStatus;
use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201004181710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM `trip_status`;');
        $this->addSql('INSERT INTO `trip_status` VALUES
            (' . TripStatus::SCHEDULED . ', "Programada", "' . Identifier::database() . '"),
            (' . TripStatus::STARTED . ', "Iniciada", "' . Identifier::database() . '"),
            (' . TripStatus::DONE . ', "Concluída", "' . Identifier::database() . '"),
            (' . TripStatus::NON_PRODUCTIVE . ', "Não produtiva", "' . Identifier::database() . '");
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `trip_status`;');
        $this->addSql('INSERT INTO `trip_status` VALUES
            ("1", "Não realizada", "' . Identifier::database() . '"),
            ("2", "Realizada", "' . Identifier::database() . '"),
            ("3", "Programada", "' . Identifier::database() . '")
        ;');
    }
}
