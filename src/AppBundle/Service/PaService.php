<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;

class PaService
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(EntityDir\User::class);
        $this->reportRepository = $em->getRepository(EntityDir\Report\Report::class);
        $this->clientRepository = $em->getRepository(EntityDir\Client::class);
        $this->log = [];
    }

    /**
     * //TODO
     * - move to methods
     * - cleanup data if needed
     *
     * Example of a single row :[
     *     'Email'        => 'dep2@provider.com',
     *     'Deputy No'    => '00000001',
     *     'Dep Postcode' => 'N1 ABC',
     *     'Dep Forename' => 'Dep1',
     *     'Dep Surname'  => 'Uty2',
     *     'Dep Type'     => 23,
     *     'Dep Adrs1'    => 'ADD1',
     *     'Dep Adrs2'    => 'ADD2',
     *     'Dep Adrs3'    => 'ADD3',
     *     'Dep Adrs4'    => 'ADD4',
     *     'Dep Adrs5'    => 'ADD5',
     *
     *     'Case'       => '10000003',
     *     'Forename'   => 'Cly3',
     *     'Surname'    => 'Hent3',
     *     'Corref'     => 'A3',
     *     'Report Due' => '05-Feb-15',
     * ]
     *
     * @param array $rows
     *
     * @return array
     */
    public function addFromCasrecRows(array $rows)
    {
        $this->added = ['users' => [], 'clients' => [], 'reports' => []];
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                if ($row['Dep Type'] != 23) {
                    throw new \RuntimeException('Not a PA');
                }

                $user = $this->createUser($row);
                $client = $this->createClient($row, $user);
                $this->createReport($row, $client);
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage() . ' in line ' . ($index + 2);
            }
            // clean up for next iteration
            $this->em->clear();
        }

        return ['added' => $this->added, 'errors' => $errors];
    }

    /**
     * @param array $row
     *
     * @return EntityDir\User
     */
    private function createUser(array $row)
    {
        $email = $row['Email'];
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $user = new EntityDir\User();
            $user
                ->setRegistrationDate(new \DateTime())
                ->setDeputyNo($row['Deputy No'])
                ->setEmail($email)
                ->setFirstname($row['Dep Forename'])
                ->setLastname($row['Dep Surname'])
                ->setRoleName(EntityDir\User::ROLE_PA)
                ->setAddress1($row['Dep Adrs1'])
                ->setAddress2($row['Dep Adrs2'])
                ->setAddress3($row['Dep Adrs3'] . ' ' . $row['Dep Adrs4'] . ' ' . $row['Dep Adrs5'])
                ->setAddressPostcode($row['Dep Postcode'])//->setAddressCountry('GB')
            ;
            $this->added['users'][] = $email;
            $this->em->persist($user);
            $this->em->flush($user);
        }

        return $user;
    }

    /**
     * @param array          $row
     * @param EntityDir\User $user
     *
     * @return EntityDir\Client
     */
    private function createClient(array $row, EntityDir\User $user)
    {
        // find or create client
        $caseNumber = strtolower($row['Case']);
        $client = $this->clientRepository->findOneBy(['caseNumber' => $caseNumber]);
        if ($client) {
            foreach ($client->getUsers() as $cu) {
                $client->getUsers()->removeElement($cu);
            }
        } else {
            $client = new EntityDir\Client();
            $client
                ->setCaseNumber($caseNumber)
                ->setFirstname(trim($row['Forename']))
                ->setLastname(trim($row['Surname']))//->setCourtDate($row['Dship Create'])
            ;
            $this->added['clients'][] = $client->getCaseNumber();
            $this->em->persist($client);
        }
        $user->addClient($client);
        $this->em->flush($client);

        return $client;
    }

    /**
     * @param array            $row
     * @param EntityDir\Client $client
     *
     * @return EntityDir\Report\Report
     */
    private function createReport(array $row, EntityDir\Client $client)
    {
        // find or create reports
        $reportDueDate = self::parseDate($row['Report Due']);
        if (!$reportDueDate) {
            throw new \RuntimeException("Cannot parse date {$row['Report Due']}");
        }
        $report = $client->getReportByDueDate($reportDueDate);
        if (!$report) {
            $report = new EntityDir\Report\Report();
            $client->addReport($report);
            $report
                ->setType(EntityDir\Report\Report::TYPE_102)
                ->setEndDate($reportDueDate);
            $this->added['reports'][] = $client->getCaseNumber() . '-' . $reportDueDate->format('Y-m-d');
            $this->em->persist($report);
            $this->em->flush();
        }

        return $report;
    }

    /**
     * create DateTime object based on '16-Dec-2014' formatted dates
     *
     * @param string $dateString e.g. 16-Dec-2014
     *
     * @return \DateTime
     */
    private static function parseDate($dateString)
    {
        return \DateTime::createFromFormat('d-M-Y', $dateString);
    }
}