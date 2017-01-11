<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version099 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE report_type (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, display_order INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE report ADD report_type_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A5D5F193 FOREIGN KEY (report_type_id) REFERENCES report_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C42F7784A5D5F193 ON report (report_type_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784A5D5F193');
        $this->addSql('DROP TABLE report_type');
        $this->addSql('DROP INDEX IDX_C42F7784A5D5F193');
        $this->addSql('ALTER TABLE report DROP report_type_id');
    }
}
