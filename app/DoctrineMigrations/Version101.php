<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version101 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE expense_category (id SERIAL NOT NULL, report_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C02DDB384BD2A4C0 ON expense_category (report_id)');
        $this->addSql('CREATE TABLE income_benefit_type (id SERIAL NOT NULL, report_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2DB1C1B84BD2A4C0 ON income_benefit_type (report_id)');
        $this->addSql('CREATE TABLE income_category (id SERIAL NOT NULL, report_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2F2D922F4BD2A4C0 ON income_category (report_id)');
        $this->addSql('ALTER TABLE expense_category ADD CONSTRAINT FK_C02DDB384BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income_benefit_type ADD CONSTRAINT FK_2DB1C1B84BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income_category ADD CONSTRAINT FK_2F2D922F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ALTER type DROP DEFAULT');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE expense_category');
        $this->addSql('DROP TABLE income_benefit_type');
        $this->addSql('DROP TABLE income_category');
        $this->addSql('ALTER TABLE report ALTER type SET DEFAULT \'102\'');
    }
}
