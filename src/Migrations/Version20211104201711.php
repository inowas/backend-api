<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211104201711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS modflow_model_packages (id VARCHAR(255) NOT NULL, data TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE modflow_model_packages ADD CONSTRAINT FK_96B8ED89BF396750 FOREIGN KEY (id) REFERENCES modflow_model (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('INSERT INTO modflow_model_packages (id, data) select id, packages from modflow_model;');
        $this->addSql('ALTER TABLE modflow_model DROP packages');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE modflow_model ADD packages TEXT NOT NULL DEFAULT \'[]\'');
        $this->addSql('UPDATE modflow_model SET packages = modflow_model_packages.data FROM modflow_model_packages WHERE modflow_model.id = modflow_model_packages.id');
        $this->addSql('DROP TABLE modflow_model_packages');

    }
}
