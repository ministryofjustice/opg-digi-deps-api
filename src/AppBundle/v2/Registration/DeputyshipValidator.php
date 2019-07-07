<?php

namespace AppBundle\v2\Registration;

use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;

class DeputyshipValidator
{
    /** @var ClientRepository */
    private $clientRepository;

    /**
     * @param ClientRepository $clientRepository
     */
    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param $caseNumber
     * @param $deputyNumber
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function clientExistsUnderDifferentDeputyship($caseNumber, $deputyNumber, $deputyRole)
    {
        // if client not exist return false
        // if existing client not attached return false
        // if existing attached client is attached to this deputy return false
        // otherwise return true

        $res =  $this->clientRepository->clientIsAttachedButNotToThisDeputy($caseNumber, $deputyNumber);

        return $res;
    }
}
