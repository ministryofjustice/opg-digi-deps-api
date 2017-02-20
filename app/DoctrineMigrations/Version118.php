<?php

namespace Application\Migrations;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version118 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // add user.role_name with values depending on role_id column
        $this->addSql('ALTER TABLE dd_user ADD role_name VARCHAR(50) DEFAULT NULL');
        $this->addSql("UPDATE dd_user SET role_name ='ROLE_ADMIN' WHERE role_id = 1 ");
        $this->addSql("UPDATE dd_user SET role_name ='ROLE_LAY_DEPUTY' WHERE role_id = 2");
        $this->addSql("UPDATE dd_user SET role_name ='ROLE_AD' WHERE role_id = 5");
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