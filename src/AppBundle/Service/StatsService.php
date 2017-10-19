<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class StatsService
{
    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->userRepository = $em->getRepository(User::class);
        $this->reportRepository = $em->getRepository(Report::class);
    }

    /**
     * @param int $maxResults
     *
     * @return array
     */
    public function getRecords($maxResults = null)
    {
        $ret = [];
        $qb = $this->userRepository->createQueryBuilder('u');
        $qb
            ->leftJoin('u.clients', 'c')
            ->where('u.roleName = ?1')
            ->orderBy('u.id', 'DESC');
        $qb->setParameter('1', 'ROLE_LAY_DEPUTY');

        if ($maxResults) {
            $qb->setMaxResults($maxResults);
        }
        $query = $qb->getQuery();

        $users = $query->getResult();

        foreach ($users as $user) {
            /* @var $user User */
            $row = [
                'id'                      => $user->getId(),
                'email'                   => $user->getEmail(),
                'name'                    => $user->getFirstname(),
                'lastname'                => $user->getLastname(),
                'registration_date'       => $user->getRegistrationDate() ? $user->getRegistrationDate()->format('Y-m-d') : '-',
                'report_date_due'         => 'n/a',
                'report_date_submitted'   => 'n/a',
                'last_logged_in'          => $user->getLastLoggedIn() ? $user->getLastLoggedIn()->format('Y-m-d H:i:s') : '-',
                'client_name'             => 'n.a.',
                'client_lastname'         => 'n.a.',
                'client_casenumber'       => 'n.a.',
                'client_court_order_date' => 'n.a.',
                'total_reports'           => 0,
                'active_reports'          => 0,
            ];

            foreach ($user->getClients() as $client) {
                $row['client_name'] = $client->getFirstname();
                $row['client_lastname'] = $client->getLastname();
                $row['client_casenumber'] = $client->getCaseNumber();
                $row['client_court_order_date'] = $client->getCourtDate() ? $client->getCourtDate()->format('Y-m-d') : '-';
                foreach ($client->getReports() as $report) {
                    ++$row['total_reports'];
                    if ($report->getSubmitted()) {
                        continue;
                    }
                    ++$row['active_reports'];
                }
            }

            $activeReportId = $user->getActiveReportId();
            if ($activeReportId) {
                $report = $this->reportRepository->find($activeReportId);
                $row['report_date_due'] = $report->getDueDate()->format('Y-m-d');

                //Fill in the last submitted column with the submission date of the last submitted report
                if (!$user->isPaDeputy()) {
                    $clients = $user->getClients();
                    $client = !empty($clients) ? $clients->first() : null;
                    if ($client != null) {
                        $submittedReports = $client->getSubmittedReports();
                        $lastSubmittedReport = !empty($submittedReports) ? $submittedReports->first() : null;
                        if ($lastSubmittedReport != null) {
                            $row['report_date_submitted'] = $lastSubmittedReport->getSubmitDate()->format('Y-m-d');
                        }
                    }
                }
            }

            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * @param string $filePath
     * @param int $maxResults
     *
     * @return string
     */
    public function saveCsv($filePath, $maxResults = null)
    {
        $records = $this->getRecords($maxResults);
        $linesWritten = 0;

        $f = fopen($filePath, 'w');
        fputcsv($f, array_keys($records[0]));
        foreach ($records as $row) {
            fputcsv($f, $row);
            $linesWritten++;
        }
        fclose($f);

        return $linesWritten;
    }
}
