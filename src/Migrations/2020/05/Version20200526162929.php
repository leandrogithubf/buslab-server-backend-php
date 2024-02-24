<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200526162929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, model_id INT NOT NULL, company_id INT NOT NULL, odb_id INT NOT NULL, prefix INT NOT NULL, plate VARCHAR(255) NOT NULL, consumption_target DOUBLE PRECISION NOT NULL, start_operation VARCHAR(255) NOT NULL, identifier VARCHAR(15) NOT NULL, is_active TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_1B80E4867975B7E7 (model_id), INDEX IDX_1B80E486979B1AD6 (company_id), UNIQUE INDEX UNIQ_1B80E4862CFDEE0B (odb_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4867975B7E7 FOREIGN KEY (model_id) REFERENCES vehicle_model (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4862CFDEE0B FOREIGN KEY (odb_id) REFERENCES obd (id)');

        $this->addSql('ALTER TABLE vehicle_brand ADD identifier VARCHAR(15) NOT NULL');

        $this->addSql('ALTER TABLE vehicle_model ADD identifier VARCHAR(15) NOT NULL');

        $this->addSql('ALTER TABLE vehicle_model ADD brand_id INT NOT NULL, CHANGE fuel_density fuel_density DOUBLE PRECISION NOT NULL, CHANGE air_fuel_ratio air_fuel_ratio DOUBLE PRECISION NOT NULL, CHANGE efficiency efficiency DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE vehicle_model ADD CONSTRAINT FK_B53AF23544F5D008 FOREIGN KEY (brand_id) REFERENCES vehicle_brand (id)');
        $this->addSql('CREATE INDEX IDX_B53AF23544F5D008 ON vehicle_model (brand_id)');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E4867975B7E7');
        $this->addSql('DROP INDEX IDX_1B80E4867975B7E7 ON vehicle');
        $this->addSql('ALTER TABLE vehicle DROP model_id');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE vehicle');

        $this->addSql('ALTER TABLE vehicle_brand DROP identifier');
        $this->addSql('ALTER TABLE vehicle_model DROP identifier');

        $this->addSql('ALTER TABLE vehicle ADD model_id INT NOT NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4867975B7E7 FOREIGN KEY (model_id) REFERENCES vehicle_model (id)');
        $this->addSql('CREATE INDEX IDX_1B80E4867975B7E7 ON vehicle (model_id)');
        $this->addSql('ALTER TABLE vehicle_model DROP FOREIGN KEY FK_B53AF23544F5D008');
        $this->addSql('DROP INDEX IDX_B53AF23544F5D008 ON vehicle_model');
        $this->addSql('ALTER TABLE vehicle_model DROP brand_id, CHANGE efficiency efficiency DOUBLE PRECISION DEFAULT NULL, CHANGE air_fuel_ratio air_fuel_ratio DOUBLE PRECISION DEFAULT NULL, CHANGE fuel_density fuel_density DOUBLE PRECISION DEFAULT NULL');
    }
}
