<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version189 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // First migration - joins on money transfer table so only reports that have money transfers should be updated with the correct value of FALSE
        $this->addSql('UPDATE report r SET no_transfers_to_add = FALSE FROM money_transfer m WHERE r.id = m.report_id');

        // 2nd migration
        $this->addSql('
            UPDATE report r
              SET no_transfers_to_add = TRUE
              FROM (select r.id, m.amount FROM report r LEFT OUTER JOIN money_transfer m ON r.id = m.report_id) s
              WHERE r.id = s.id
                AND s.amount IS NULL'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

    }
}
