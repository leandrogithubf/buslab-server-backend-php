<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200911021909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updating parameter configuration structure';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE parameter_configuration DROP FOREIGN KEY FK_D98F3F1DB9CF4E62');
        $this->addSql('DROP INDEX IDX_D98F3F1DB9CF4E62 ON parameter_configuration');
        $this->addSql('ALTER TABLE parameter_configuration ADD max_allowed VARCHAR(255) DEFAULT NULL, ADD min_allowed VARCHAR(255) DEFAULT NULL, DROP green_belt, DROP red_belt, DROP max_speed, DROP max_years_op, DROP idleness, DROP delay_tolerance_start, DROP delay_tolerance_end, DROP acc_max_sprint, DROP acc_max_motion, DROP convoy, CHANGE event_category_id parameter_id INT NOT NULL');
        $this->addSql('ALTER TABLE parameter_configuration ADD CONSTRAINT FK_D98F3F1D7C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameter (id)');
        $this->addSql('CREATE INDEX IDX_D98F3F1D7C56DBD6 ON parameter_configuration (parameter_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE parameter_configuration DROP FOREIGN KEY FK_D98F3F1D7C56DBD6');
        $this->addSql('DROP INDEX IDX_D98F3F1D7C56DBD6 ON parameter_configuration');
        $this->addSql('ALTER TABLE parameter_configuration ADD green_belt VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD red_belt VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD max_speed VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD max_years_op VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD idleness VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD delay_tolerance_start VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD delay_tolerance_end VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD acc_max_sprint VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD acc_max_motion VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD convoy TIME DEFAULT NULL, DROP max_allowed, DROP min_allowed, CHANGE parameter_id event_category_id INT NOT NULL');
        $this->addSql('ALTER TABLE parameter_configuration ADD CONSTRAINT FK_D98F3F1DB9CF4E62 FOREIGN KEY (event_category_id) REFERENCES event_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_D98F3F1DB9CF4E62 ON parameter_configuration (event_category_id)');
    }
}
