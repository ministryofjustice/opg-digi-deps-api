<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170119102445 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE expense_category (id INT NOT NULL, name TEXT NOT NULL, code TEXT NOT NULL, display_order INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE expense_type (id INT NOT NULL, expense_category_id INT DEFAULT NULL, name TEXT NOT NULL, code TEXT NOT NULL, display_order INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3879194B6B2A3179 ON expense_type (expense_category_id)');
        $this->addSql('CREATE TABLE income (id SERIAL NOT NULL, report_id INT DEFAULT NULL, income_type_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, income_date DATE DEFAULT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3FA862D04BD2A4C0 ON income (report_id)');
        $this->addSql('CREATE INDEX IDX_3FA862D07D0EFAEA ON income (income_type_id)');
        $this->addSql('CREATE TABLE income_category (id INT NOT NULL, name TEXT NOT NULL, code TEXT NOT NULL, display_order INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE income_type (id INT NOT NULL, income_category_id INT DEFAULT NULL, name TEXT NOT NULL, code TEXT NOT NULL, display_order INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4694410753F8702F ON income_type (income_category_id)');
        $this->addSql('CREATE TABLE odr_expense (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, explanation TEXT NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_92A22FF97CE4B994 ON odr_expense (odr_id)');
        $this->addSql('CREATE TABLE money_in_category (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, display_order INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE money_out_category (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, display_order INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE report_income_category (report_id INT NOT NULL, income_category_id INT NOT NULL, PRIMARY KEY(report_id, income_category_id))');
        $this->addSql('CREATE INDEX IDX_1C913024BD2A4C0 ON report_income_category (report_id)');
        $this->addSql('CREATE INDEX IDX_1C9130253F8702F ON report_income_category (income_category_id)');
        $this->addSql('CREATE TABLE report_expense_category (report_id INT NOT NULL, expense_category_id INT NOT NULL, PRIMARY KEY(report_id, expense_category_id))');
        $this->addSql('CREATE INDEX IDX_85DC63CC4BD2A4C0 ON report_expense_category (report_id)');
        $this->addSql('CREATE INDEX IDX_85DC63CC6B2A3179 ON report_expense_category (expense_category_id)');
        $this->addSql('CREATE TABLE report_benefit_type (report_id INT NOT NULL, income_type_id INT NOT NULL, PRIMARY KEY(report_id, income_type_id))');
        $this->addSql('CREATE INDEX IDX_C64F51C14BD2A4C0 ON report_benefit_type (report_id)');
        $this->addSql('CREATE INDEX IDX_C64F51C17D0EFAEA ON report_benefit_type (income_type_id)');
        $this->addSql('CREATE TABLE report_income (report_id INT NOT NULL, income_id INT NOT NULL, PRIMARY KEY(report_id, income_id))');
        $this->addSql('CREATE INDEX IDX_165BB74F4BD2A4C0 ON report_income (report_id)');
        $this->addSql('CREATE INDEX IDX_165BB74F640ED2C0 ON report_income (income_id)');
        $this->addSql('CREATE TABLE report_expense (report_id INT NOT NULL, income_id INT NOT NULL, PRIMARY KEY(report_id, income_id))');
        $this->addSql('CREATE INDEX IDX_4DA3F0A64BD2A4C0 ON report_expense (report_id)');
        $this->addSql('CREATE INDEX IDX_4DA3F0A6640ED2C0 ON report_expense (income_id)');
        $this->addSql('CREATE TABLE report_type (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, display_order INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE expense_type ADD CONSTRAINT FK_3879194B6B2A3179 FOREIGN KEY (expense_category_id) REFERENCES expense_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_3FA862D04BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_3FA862D07D0EFAEA FOREIGN KEY (income_type_id) REFERENCES income_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income_type ADD CONSTRAINT FK_4694410753F8702F FOREIGN KEY (income_category_id) REFERENCES income_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_expense ADD CONSTRAINT FK_92A22FF97CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_income_category ADD CONSTRAINT FK_1C913024BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_income_category ADD CONSTRAINT FK_1C9130253F8702F FOREIGN KEY (income_category_id) REFERENCES income_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_expense_category ADD CONSTRAINT FK_85DC63CC4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_expense_category ADD CONSTRAINT FK_85DC63CC6B2A3179 FOREIGN KEY (expense_category_id) REFERENCES expense_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_benefit_type ADD CONSTRAINT FK_C64F51C14BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_benefit_type ADD CONSTRAINT FK_C64F51C17D0EFAEA FOREIGN KEY (income_type_id) REFERENCES income_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_income ADD CONSTRAINT FK_165BB74F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_income ADD CONSTRAINT FK_165BB74F640ED2C0 FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_expense ADD CONSTRAINT FK_4DA3F0A64BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_expense ADD CONSTRAINT FK_4DA3F0A6640ED2C0 FOREIGN KEY (income_id) REFERENCES income_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expense DROP CONSTRAINT fk_2d3a8da67ce4b994');
        $this->addSql('DROP INDEX idx_2d3a8da67ce4b994');
        $this->addSql('ALTER TABLE expense ADD expense_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expense ADD expense_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE expense RENAME COLUMN odr_id TO report_id');
        $this->addSql('ALTER TABLE expense RENAME COLUMN explanation TO description');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA64BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6A857C7A9 FOREIGN KEY (expense_type_id) REFERENCES expense_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2D3A8DA64BD2A4C0 ON expense (report_id)');
        $this->addSql('CREATE INDEX IDX_2D3A8DA6A857C7A9 ON expense (expense_type_id)');
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
        $this->addSql('ALTER TABLE expense_type DROP CONSTRAINT FK_3879194B6B2A3179');
        $this->addSql('ALTER TABLE report_expense_category DROP CONSTRAINT FK_85DC63CC6B2A3179');
        $this->addSql('ALTER TABLE expense DROP CONSTRAINT FK_2D3A8DA6A857C7A9');
        $this->addSql('ALTER TABLE report_income DROP CONSTRAINT FK_165BB74F640ED2C0');
        $this->addSql('ALTER TABLE income_type DROP CONSTRAINT FK_4694410753F8702F');
        $this->addSql('ALTER TABLE report_income_category DROP CONSTRAINT FK_1C9130253F8702F');
        $this->addSql('ALTER TABLE income DROP CONSTRAINT FK_3FA862D07D0EFAEA');
        $this->addSql('ALTER TABLE report_benefit_type DROP CONSTRAINT FK_C64F51C17D0EFAEA');
        $this->addSql('ALTER TABLE report_expense DROP CONSTRAINT FK_4DA3F0A6640ED2C0');
        $this->addSql('DROP TABLE expense_category');
        $this->addSql('DROP TABLE expense_type');
        $this->addSql('DROP TABLE income');
        $this->addSql('DROP TABLE income_category');
        $this->addSql('DROP TABLE income_type');
        $this->addSql('DROP TABLE odr_expense');
        $this->addSql('DROP TABLE money_in_category');
        $this->addSql('DROP TABLE money_out_category');
        $this->addSql('DROP TABLE report_income_category');
        $this->addSql('DROP TABLE report_expense_category');
        $this->addSql('DROP TABLE report_benefit_type');
        $this->addSql('DROP TABLE report_income');
        $this->addSql('DROP TABLE report_expense');
        $this->addSql('DROP TABLE report_type');
        $this->addSql('ALTER TABLE report ALTER type SET DEFAULT \'102\'');
        $this->addSql('ALTER TABLE expense DROP CONSTRAINT FK_2D3A8DA64BD2A4C0');
        $this->addSql('DROP INDEX IDX_2D3A8DA64BD2A4C0');
        $this->addSql('DROP INDEX IDX_2D3A8DA6A857C7A9');
        $this->addSql('ALTER TABLE expense ADD odr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expense DROP report_id');
        $this->addSql('ALTER TABLE expense DROP expense_type_id');
        $this->addSql('ALTER TABLE expense DROP expense_date');
        $this->addSql('ALTER TABLE expense RENAME COLUMN description TO explanation');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT fk_2d3a8da67ce4b994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_2d3a8da67ce4b994 ON expense (odr_id)');
    }
}
