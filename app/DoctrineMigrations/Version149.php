<?php

namespace Application\Migrations;

use AppBundle\Entity\Report\Report;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version149 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        /* @var $pdo \PDO */
        $pdo = $this->container->get('doctrine.orm.entity_manager')->getConnection();

        $paReportIdToType = $pdo->query("SELECT r.id, r.type FROM report r
          LEFT JOIN client c on r.client_id = c.id
          LEFT JOIN deputy_case dc on dc.client_id = c.id
          LEFT JOIN dd_user u on u.id = dc.user_id
          WHERE u.role_name LIKE 'ROLE_PA%'
        ")->fetchAll(\PDO::FETCH_KEY_PAIR);

        $pdo->beginTransaction();
        foreach($paReportIdToType as $reportId => $reportType) {
            $map = [
                '102'=>'102-6',
                '103'=>'103-6',
                '104'=>'104-6'
            ];
            if (isset($map[$reportType])) {
                $newReportType = $map[$reportType];
                $pdo->query("UPDATE report SET type = '$newReportType' WHERE id=$reportId")->execute();
            } else {
                echo "Can't change type for PA report $reportType, not found in mapping\n";
            }
        }
        $pdo->commit();

        /*
         * to test: this should be empty
         *
         SELECT r.id, r.type FROM report r
           LEFT JOIN client c on r.client_id = c.id
           LEFT JOIN deputy_case dc on dc.client_id = c.id
           LEFT JOIN dd_user u on u.id = dc.user_id
           WHERE u.role_name LIKE 'ROLE_PA%' AND r.type NOT LIKE '%-6';
         *
         *
         * */


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