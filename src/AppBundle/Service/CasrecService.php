<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CasrecService
{
    const STATS_NOT_OLDER_THAN = '-60 minutes';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ReportService
     */
    protected $reportService;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * CasrecService constructor.
     *
     * @param EntityManager      $em
     * @param LoggerInterface    $logger
     * @param ReportService      $reportService
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManager $em, LoggerInterface $logger, ReportService $reportService, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->reportService = $reportService;
        $this->validator = $validator;
    }

    /**
     * Updates a single CASREC record, with stats
     * Called when records are uploaded, or "updateAllCasrecRecordsWithStats" is called via cron
     *
     * @param CasRec $casrec
     */
    private function updateCasrecStatsSingle(CasRec $casrec)
    {
        // add user info, by matching DeputyNo
        // TEST identifies existing Deputy and copies meta data from first matching Deputy to CasRec if found.
        $deputyNo = $casrec->getDeputyNo();
        $results = $this->em->createQuery('SELECT u FROM ' . User::class . ' u WHERE u.deputyNo = :d1 OR u.deputyNo = :d2')
            ->setParameter('d1', strtoupper($deputyNo))
            ->setParameter('d2', strtolower($deputyNo))
            ->getResult();
        if ($results && $results[0] instanceof User) {
            $casrec->setLastLoggedIn($results[0]->getLastLoggedIn())->setRegistrationDate($results[0]->getRegistrationDate());
        }

        // add report info, by matching case number
        // TEST identifies existing Clients and copies meta data from first matching Client (and their Reports) to CasRec if found.
        $caseNumber = $casrec->getCaseNumber();
        $results = $this->em->createQuery('SELECT c FROM ' . Client::class . ' c WHERE c.caseNumber = :c1 OR c.caseNumber = :c2')
            ->setParameter('c1', strtoupper($caseNumber))
            ->setParameter('c2', strtolower($caseNumber))
            ->getResult();
        if ($results && $results[0] instanceof Client) {
            $client = $results[0]; /* @var $client Client */
            // last report is currently ordered
            $submittedReports = $client->getSubmittedReports();
            $lastReport = count($submittedReports) > 0  ? $submittedReports->first() : null;

            $casrec
                ->setNOfReportsSubmitted(count($submittedReports))
                ->setLastReportSubmittedAt($lastReport ? $lastReport->getSubmitDate() : null)
                ->setNdrSubmittedAt($client->getNdr() && $client->getNdr()->getSubmitted() ? $client->getNdr()->getSubmitDate() : null)
                ->setNOfReportsActive(count($results[0]->getUnsubmittedReports()));
        }

        // TEST sets updated timestamp on CasRec.
        $casrec->setUpdatedAt(new \DateTime());
    }

    /**
     * Launched from cron
     *
     * @return int number of changed records
     */
    public function updateAllCasrecRecordsWithStats()
    {
        $chunkSize = 50;
        $nOfRecordsUpdated = 0;

        while ($records = $this->em
            ->createQuery('SELECT c from ' . CasRec::class . ' c WHERE  (c.updatedAt < :d OR c.updatedAt IS NULL) ORDER BY c.updatedAt ASC')
            ->setParameter('d', new \DateTime(self::STATS_NOT_OLDER_THAN))
            ->setMaxResults($chunkSize)->getResult()) {
            foreach ($records as $record) {
                /* @var $nextRecordToUpdate CasRec */
                $this->updateCasrecStatsSingle($record);
                $nOfRecordsUpdated++;
            }
            $this->em->flush();
            $this->em->clear();
        }

        return $nOfRecordsUpdated;
    }

    /**
     * @param string $filePath
     * @param int    $maxResults
     *
     * @return string
     */
    public function saveCsv($filePath)
    {
        $filePathTmp = $filePath . '.tmp';
        $linesWritten = 0;

        /* @var $it IterableResult */
        // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/batch-processing.html
        $it = $this->em->createQuery('SELECT c FROM ' . CasRec::class . ' c')->iterate();

        $f = fopen($filePathTmp, 'w');
        // TEST converts every row in casrec table to CSV
        foreach ($it as $itRow) {
            $row = $itRow[0]->toArray();
            // TEST adds header to CSV
            if ($it->key() === 0) { // write header (only for first row)
                fputcsv($f, array_keys($row));
            }
            fputcsv($f, $row);
            $linesWritten++;
        }
        fclose($f);

        // replace file instantly
        // TEST removes existing file if exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        // TEST saves file
        rename($filePathTmp, $filePath);

        // TEST return number of lines written
        return $linesWritten;
    }

    /**
     * @param array $data
     *
     * @return array [added=>, errors=>]
     */
    public function addBulk(array $data)
    {
        $maxRecords = 10000; // memory failure above this limit
        $persistEvery = 5000; //optimised for performances

        $count = count($data);

        // TEST exception is thrown if given empty data.
        if (!$count) {
            throw new \RuntimeException('No record received from the API');
        }

        // TEST exception is thrown if count of rows exceeds maz count.
        if ($count > $maxRecords) {
            throw new \RuntimeException("Max $maxRecords records allowed in a single bulk insert");
        }

        $this->logger->notice(__METHOD__ . ': Received ' . count($data) . ' records');

        $retErrors = [];
        try {
            $this->em->beginTransaction();
            $added = 1;

            //  Load up the data array into an array of CasRec entities
            $casRecEntities = [];

            foreach ($data as $dataIndex => $row) {
                //  Create a CasRec entity from the data and add it to the array of entities
                // TEST creates a new CasRec for each row in CSV.
                $casRecEntities[] = $casRecEntity = new CasRec($row);

                //  Validate the entity before adding it the entity manager to persist
                $errors = $this->validator->validate($casRecEntity);

                // TEST ignores CSV rows with invalid column values.
                if (count($errors) > 0) {
                    $retErrors[] = 'ERROR IN LINE ' . ($dataIndex + 2) . ' :' . str_replace('Object(AppBundle\Entity\CasRec).', '', (string) $errors);
                    unset($casRecEntity);
                } else {
                    $this->updateCasrecStatsSingle($casRecEntity);

                    // TEST persists each CasRec before moving to next row in CSV.
                    $this->em->persist($casRecEntity);

                    // TEST flushes in batches
                    if (($added++ % $persistEvery) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }
                }
            }

            $this->em->flush();
            $this->logger->notice(__METHOD__ . ': flushed');

            //  Before committing the CasRec entities use the report service to update any report types if necessary
            // TEST updates Report types on any mismatches with existing Reports.
            $this->reportService->updateCurrentReportTypes($casRecEntities, User::ROLE_LAY_DEPUTY);
            $this->logger->notice(__METHOD__ . ': report types updated');

            $this->em->commit();
            $this->em->clear();
        } catch (\Exception $e) {
            // TEST exceptions are caught and returned in array format.
            return ['added' => $added - 1, 'errors' => [$e->getMessage()]];
        }

        // TEST returns array with added and errors counts.
        return ['added' => $added - 1, 'errors' => $retErrors];
    }
}
