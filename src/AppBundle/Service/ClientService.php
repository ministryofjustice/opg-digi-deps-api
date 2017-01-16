<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityRepository;

class clientService
{
    private $clientRepository;
    public function __construct(EntityRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }
    public function findOneById($id)
    {
        return $this->clientRepository->findOneBy(['id' => $id]);
    }
}
