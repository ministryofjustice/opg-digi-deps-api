<?php

namespace AppBundle\v2\Repository;

use AppBundle\v2\Assembler\DeputyAssembler;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeputyRepository
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ObjectRepository */
    private $repository;

    /** @var DeputyAssembler */
    private $deputyAssembler;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ObjectRepository $repository
     * @param DeputyAssembler $deputyAssembler
     */
    public function __construct(EntityManagerInterface $entityManager, ObjectRepository $repository, DeputyAssembler $deputyAssembler)
    {
        $this->repository = $repository;
        $this->deputyAssembler = $deputyAssembler;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $deputyId
     * @return \AppBundle\v2\DTO\DeputyDto
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDtoById($deputyId)
    {
        $dtoData = $this->getDtoDataArray($deputyId);

        return $this->deputyAssembler->assembleFromArray($dtoData);
    }

    /**
     * @param $deputyId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getDtoDataArray($deputyId)
    {
        $sql = <<<QUERY
            SELECT 
              u.id as u_id, u.firstname as u_firstname, u.lastname as u_lastname, u.email as u_email, u.role_name as u_rolename, u.address_postcode as u_postcode, u.odr_enabled as u_ndrenabled, 
              c.id as c_id, c.firstname as c_firstname, c.lastname as c_lastname, c.email as c_email, c.case_number as c_casenumber, 
              count(report.id) as c_reportCount, 
              odr.id as c_ndrId
            FROM dd_user u 
            JOIN deputy_case on deputy_case.user_id = u.id 
            JOIN client c on deputy_case.client_id = c.id
            LEFT JOIN odr on odr.client_id = c.id
            LEFT JOIN report on report.client_id = c.id
            WHERE deputy_case.user_id = :deputyId
            GROUP BY u.id, c.id, odr.id
            ORDER BY c.id DESC
QUERY;

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute(['deputyId' => $deputyId]);

        return $stmt->fetchAll();
    }
}
