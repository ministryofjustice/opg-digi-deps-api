<?php

namespace AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\User;
use AppBundle\Service\ReportService;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecCreationException;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use Doctrine\ORM\EntityManager;

class LayDeputyshipUploader
{
    /** @var EntityManager */
    protected $em;

    /** @var ClientRepository  */
    private $clientRepository;

    /** @var ReportService */
    protected $reportService;

    /** @var CasRecFactory */
    private $casRecFactory;

    /** @var int */
    private $added = 0;

    /** @var array */
    private $ignored = [];

    /** @var array */
    private $errors = [];

    /** @var array */
    private $casRecEntities = [];

    /** @var int */
    const MAX_UPLOAD = 10000;

    /** @var int */
    const FLUSH_EVERY = 5000;

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

        try {
            $this->em->beginTransaction();

            foreach ($collection as $index => $layDeputyshipDto) {
                if ($this->clientBelongsToDifferentDeputy($layDeputyshipDto)) {
                    $this->ignored[] = sprintf('%s:%s', $layDeputyshipDto->getCaseNumber(), $layDeputyshipDto->getDeputyNumber());
                    continue;
                }

                try {
                    $this->createAndPersistNewCasRecEntity($layDeputyshipDto);
                } catch (CasRecCreationException $e) {
                    $this->errors[] = sprintf('ERROR IN LINE %d: %s', $index + 2, $e->getMessage());
                    continue;
                }

                $this->handleBatchDatabaseFlush();
            }

            $this
                ->updateReportTypes()
                ->commitTransactionToDatabase();

        } catch (\Throwable $e) {
            return ['added' => $this->added, 'errors' => [$e->getMessage()]];
        }

        return [
            'added' => $this->added,
            'errors' => $this->errors,
            'ignored-count' => count($this->ignored),
            'ignored' => $this->ignored
        ];
    }

    /**
     * @param LayDeputyshipDtoCollection $collection
     */
    private function throwExceptionIfDataTooLarge(LayDeputyshipDtoCollection $collection): void
    {
        if ($collection->count() > self::MAX_UPLOAD) {
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
        $result = $this
            ->clientRepository
            ->getAttachedDeputiesIfNotAttachedToThis($layDeputyshipDto->getCaseNumber(), $layDeputyshipDto->getDeputyNumber());

        if (!$result) { return false; }

        if (count($result) === 1) {
            // A single result could be this Lay deputy but with a ',' separated list of deputy nums (Multiple Lay Deputyship)..
            // Before we rule out this result not belonging to this Lay deputy, double check for the deputy num within a string list.
            $deputyNumbers = explode(',', $result[0]['deputy_no']);

            return !in_array($layDeputyshipDto->getDeputyNumber(), $deputyNumbers);
        }

        return true;
    }

    /**
     * @param LayDeputyshipDto $layDeputyshipDto
     * @return LayDeputyshipUploader
     * @throws \Doctrine\ORM\ORMException
     */
    private function createAndPersistNewCasRecEntity(LayDeputyshipDto $layDeputyshipDto): LayDeputyshipUploader
    {
        $this->casRecEntities[] = $casRecEntity = $this->casRecFactory->createFromDto($layDeputyshipDto);

        $this->em->persist($casRecEntity);

        return $this;
    }

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function handleBatchDatabaseFlush(): void
    {
        if ((++$this->added % self::FLUSH_EVERY) === 0) {
            $this->em->flush();
            $this->em->clear();
        }
    }

    /**
     * @return LayDeputyshipUploader
     * @throws \Exception
     */
    private function updateReportTypes(): LayDeputyshipUploader
    {
        $this->reportService->updateCurrentReportTypes($this->casRecEntities, User::ROLE_LAY_DEPUTY);

        return $this;
    }

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function commitTransactionToDatabase(): void
    {
        $this->em->flush();
        $this->em->commit();
        $this->em->clear();
    }
}
