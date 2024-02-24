<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210316185236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (43, "Alta temperatura do líquido de arrefecimento do motor", "' . Identifier::database() . '",null,5);');
        $this->addSql('INSERT INTO event_category (id, description, identifier,parameter_id,sector_id) VALUES (44, "Alta temperatura do ar de admissão", "' . Identifier::database() . '",null,5);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DELETE FROM `event_category` WHERE id = 43');
        $this->addSql('DELETE FROM `event_category` WHERE id = 44');
    }
}
