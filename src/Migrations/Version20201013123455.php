<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201013123455 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE modflow_model ALTER packages TYPE TEXT');
        $this->addSql('ALTER TABLE modflow_model ALTER packages DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN modflow_model.discretization IS NULL');
        $this->addSql('COMMENT ON COLUMN modflow_model.soilmodel IS NULL');
        $this->addSql('COMMENT ON COLUMN modflow_model.boundaries IS NULL');
        $this->addSql('COMMENT ON COLUMN modflow_model.transport IS NULL');
        $this->addSql('COMMENT ON COLUMN modflow_model.variable_density IS NULL');
        $this->addSql('COMMENT ON COLUMN modflow_model.calculation IS NULL');
        $this->addSql('COMMENT ON COLUMN modflow_model.packages IS NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE modflow_model ALTER packages TYPE JSON');
        $this->addSql('ALTER TABLE modflow_model ALTER packages DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN modflow_model.discretization IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.soilmodel IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.boundaries IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.transport IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.variable_density IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.calculation IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.packages IS \'(DC2Type:json_array)\'');
    }
}
