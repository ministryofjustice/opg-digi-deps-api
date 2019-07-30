<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version213 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE address (id SERIAL NOT NULL, deputyAddressNo INT DEFAULT NULL, address1 VARCHAR(100) DEFAULT NULL, email1 VARCHAR(100) DEFAULT NULL, email2 VARCHAR(100) DEFAULT NULL, email3 VARCHAR(100) DEFAULT NULL, address2 VARCHAR(100) DEFAULT NULL, address3 VARCHAR(100) DEFAULT NULL, address4 VARCHAR(100) DEFAULT NULL, address5 VARCHAR(100) DEFAULT NULL, postcode VARCHAR(8) DEFAULT NULL, country VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE organisation (id SERIAL NOT NULL, organisation_name VARCHAR(100) NOT NULL, email_domain VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE client ADD organisation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404559E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C74404559E6B1585 ON client (organisation_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C74404559E6B1585');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP INDEX IDX_C74404559E6B1585');
        $this->addSql('ALTER TABLE client DROP organisation_id');
    }
}
