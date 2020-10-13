<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201013113617 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE mcda (id VARCHAR(255) NOT NULL, userid UUID DEFAULT NULL, criteria JSON NOT NULL, weight_assignments JSON NOT NULL, constraints JSON NOT NULL, with_ahp BOOLEAN NOT NULL, suitability JSON NOT NULL, grid_size JSON DEFAULT NULL, version VARCHAR(255) DEFAULT NULL, tool VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, is_archived BOOLEAN NOT NULL, is_scenario BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_99bd7ad664b64dcc ON mcda (userid)');
        $this->addSql('COMMENT ON COLUMN mcda.userid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mcda.criteria IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN mcda.weight_assignments IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN mcda.constraints IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN mcda.suitability IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN mcda.grid_size IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN mcda.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mcda.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE events (id INT NOT NULL, aggregate_id VARCHAR(36) NOT NULL, aggregate_name VARCHAR(64) NOT NULL, event_name VARCHAR(64) NOT NULL, version INT NOT NULL, payload JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN events.payload IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN events.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE modflow_model (id VARCHAR(255) NOT NULL, userid UUID DEFAULT NULL, discretization JSON NOT NULL, soilmodel JSON NOT NULL, boundaries JSON NOT NULL, transport JSON DEFAULT NULL, variable_density JSON DEFAULT NULL, calculation JSON NOT NULL, packages JSON NOT NULL, tool VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, is_archived BOOLEAN NOT NULL, is_scenario BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_56a58fb264b64dcc ON modflow_model (userid)');
        $this->addSql('COMMENT ON COLUMN modflow_model.userid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.discretization IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.soilmodel IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.boundaries IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.transport IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.variable_density IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.calculation IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.packages IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN modflow_model.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE simple_tool (id VARCHAR(255) NOT NULL, userid UUID DEFAULT NULL, data JSON NOT NULL, tool VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, is_archived BOOLEAN NOT NULL, is_scenario BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_8f125c6864b64dcc ON simple_tool (userid)');
        $this->addSql('COMMENT ON COLUMN simple_tool.userid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN simple_tool.data IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN simple_tool.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN simple_tool.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE users (id UUID NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, archived BOOLEAN NOT NULL, roles JSON NOT NULL, profile JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_1483a5e9f85e0677 ON users (username)');
        $this->addSql('COMMENT ON COLUMN users.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN users.roles IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN users.profile IS \'(DC2Type:json_array)\'');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE data_drops (hash VARCHAR(40) NOT NULL, userid UUID DEFAULT NULL, filename VARCHAR(64) NOT NULL, adapter VARCHAR(64) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(hash))');
        $this->addSql('CREATE INDEX idx_80a1577564b64dcc ON data_drops (userid)');
        $this->addSql('CREATE UNIQUE INDEX uniq_80a157753c0be965 ON data_drops (filename)');
        $this->addSql('COMMENT ON COLUMN data_drops.userid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN data_drops.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE mcda');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE events');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE modflow_model');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE simple_tool');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE users');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE data_drops');
    }
}
