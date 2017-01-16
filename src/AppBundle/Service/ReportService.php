<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report as ReportEntity;
use Doctrine\ORM\EntityRepository;
use JMS\Serializer\Serializer;

class ReportService
{
    /** @var  EntityRepository */
    protected $reportRepository;
    /** @var ClientService */
    protected $clientService;
    /** @var  Serializer */
    protected $serializer;

    public function __construct(EntityRepository $reportRepository, ClientService $clientService, $serializer)
    {
        $this->reportRepository = $reportRepository;
        $this->clientService = $clientService;
        $this->serializer = $serializer;
    }

    public function findById($id)
    {
        return $this->reportRepository->findOneBy(['id' => $id]);
    }

    public function create($payload)
    {
        $reportEntity = $this->serializer->deserialize($payload, ReportEntity::class, 'json');
        $reportEntity->setReportSeen(true);
        $this->reportRepository->save($reportEntity);

        return $reportEntity;
    }
}
