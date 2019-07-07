<?php

namespace AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\User;
use AppBundle\Service\ReportService;
use AppBundle\v2\Registration\DeputyshipValidator;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecCreationException;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use Doctrine\ORM\EntityManager;

class CasRecLayDeputyshipUploader implements LayDeputyshipUploaderInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var ClientRepository  */
    private $clientRepository;

    /** @var ReportService */
    protected $reportService;

    /** @var CasRecFactory */
    private $casRecFactory;

    /** @var array */
    private $errors = [];

    /** @var int */
    const MAX_UPLOAD = 10000;

    /** @var int */
    const PERSIST_EVERY = 5000;

    /**
     * @param EntityManager $em
     * @param ClientRepository $clientRepository
     * @param ReportService $reportService
     * @param CasRecFactory $casRecFactory
     */
    public function __construct(
        EntityManager $em,
        ClientRepository $clientRepository,
        ReportService $reportService,
        CasRecFactory $casRecFactory
    ) {
        $this->em = $em;
        $this->clientRepository = $clientRepository;
        $this->reportService = $reportService;
        $this->casRecFactory = $casRecFactory;
    }

    /**
     * @param LayDeputyshipDtoCollection $collection
     * @return array
     */
    public function upload(LayDeputyshipDtoCollection $collection): array
    {
        $this->throwExceptionIfDataTooLarge($collection);

        $added = 0;
        $casRecEntities = [];

        try {
            $this->em->beginTransaction();

            foreach ($collection as $index => $layDeputyshipDto) {

                if ($this->clientBelongsToDifferentDeputy($layDeputyshipDto)) { continue; }

                try {
                    $casRecEntities[] = $this->createAndPersistNewCasRecEntity($layDeputyshipDto);

                    if ((++$added % self::PERSIST_EVERY) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }
                } catch (CasRecCreationException $e) {
                    $this->logError($index + 2, $e->getMessage());
                    continue;
                }
            }

            $this->em->flush();
            $this->reportService->updateCurrentReportTypes($casRecEntities, User::ROLE_LAY_DEPUTY);
            $this->em->commit();
            $this->em->clear();

        } catch (\Throwable $e) {
            return ['added' => $added - 1, 'errors' => [$e->getMessage()]];
        }

        return ['added' => $added, 'errors' => $this->errors];
    }

    /**
     * @param LayDeputyshipDtoCollection $collection
     */
    private function throwExceptionIfDataTooLarge(LayDeputyshipDtoCollection $collection): void
    {
        if (count($collection) > self::MAX_UPLOAD) {
            throw new \RuntimeException(sprintf(
                'Max %d records allowed in a single bulk insert',
                self::MAX_UPLOAD
            ));
        }
    }

    /**
     * @param LayDeputyshipDto $layDeputyshipDto
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    private function clientBelongsToDifferentDeputy(LayDeputyshipDto $layDeputyshipDto): bool
    {
        return $this
            ->clientRepository
            ->clientIsAttachedButNotToThisDeputy($layDeputyshipDto->getCaseNumber(), $layDeputyshipDto->getDeputyNumber());
    }

    /**
     * @param LayDeputyshipDto $layDeputyshipDto
     * @return CasRec
     * @throws \Doctrine\ORM\ORMException
     */
    private function createAndPersistNewCasRecEntity(LayDeputyshipDto $layDeputyshipDto): CasRec
    {
        $casRecEntity = $this->casRecFactory->createFromDto($layDeputyshipDto);

        $this->em->persist($casRecEntity);

        return $casRecEntity;
    }

    /**
     * @param $line
     * @param $message
     * @return array
     */
    private function logError($line, $message): array
    {
        $this->errors[] = sprintf('ERROR IN LINE %d: %s', $line, $message);
    }
}
