<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200807031845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creating notificationfields/tables to attach to user';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_event_category (user_id INT NOT NULL, event_category_id INT NOT NULL, INDEX IDX_2B252F48A76ED395 (user_id), INDEX IDX_2B252F48B9CF4E62 (event_category_id), PRIMARY KEY(user_id, event_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_event_category ADD CONSTRAINT FK_2B252F48A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_event_category ADD CONSTRAINT FK_2B252F48B9CF4E62 FOREIGN KEY (event_category_id) REFERENCES event_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD is_notification_enabled TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_event_category');
        $this->addSql('ALTER TABLE user DROP is_notification_enabled');
    }
}
