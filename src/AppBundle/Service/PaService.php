<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class PaService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * @var array
     */
    protected $added = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $warnings = [];

    /**
     * PaService constructor.
     *
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
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
    public function addFromCasrecRows(array $data)
    {
        $this->log('Received ' . count($data) . ' records');

        $this->added = ['users' => [], 'clients' => [], 'reports' => []];
        $errors = [];
        foreach ($data as $index => $row) {
            $row = array_map('trim', $row);
            try {
                if ($row['Dep Type'] != 23) {
                    throw new \RuntimeException('Not a PA');
                }

                $user = $this->upsertUser($row);
                $client = $this->upsertClient($row, $user);
                $this->upsertReport($row, $client, $user);
            } catch (\Exception $e) {
                $message = 'Error for Case: ' . $row['Case'] . ' for PA Deputy No: ' . $row['Deputy No'] . ': ' . $e->getMessage();
                $errors[] = $message;
            }
        }

        sort($this->added['users']);
        sort($this->added['clients']);
        sort($this->added['reports']);

        return [
            'added'    => $this->added,
            'errors'   => $errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * @param array $row
     *
     * @return EntityDir\User
     */
    private function upsertUser(array $row)
    {
        $criteria = ['deputyNo' => $row['Deputy No'], 'roleName' => EntityDir\User::ROLE_PA];
        $user = $this->userRepository->findOneBy($criteria);
        $userEmail = strtolower($row['Email']);

        if ($user) {
            // Notify email change
            if ($user->getEmail() !== $userEmail) {
                $this->warnings[$user->getDeputyNo()] = 'Deputy ' . $user->getDeputyNo() .
                    ' has changed their email to ' . $user->getEmail() . '. ' .
                    'Please update the CSV to reflect the new email address.<br />';
            }
        } else {
            // create user
            $this->log('Creating user');
            // check for duplicate email address
            $user = $this->userRepository->findOneBy(['email' => $userEmail]);
            if ($user) {
                $this->warnings[] = 'Deputy ' . $row['Deputy No'] .
                    ' cannot be added with email ' . $user->getEmail() .
                    '. Email already taken by Deputy No: ' . $user->getDeputyNo();
            } else {
                $user = new EntityDir\User();
                $user
                    ->setRegistrationDate(new \DateTime())
                    ->setDeputyNo($row['Deputy No'])
                    ->setEmail($row['Email'])
                    ->setFirstname($row['Dep Forename'])
                    ->setLastname($row['Dep Surname'])
                    ->setRoleName(EntityDir\User::ROLE_PA);

                // create team (if not already existing)
                if ($user->getTeams()->isEmpty()) {
                    // Dep Surname in the CSV is actually the PA team name
                    // it's used for firstname/lastname of the named PA (ROLE_PA), but those fields are editable
                    // and not reliable for a PA team name
                    $team = new EntityDir\Team($row['Dep Surname']);

                    // Address from upload is the team's address, not the user's
                    if (!empty($row['Dep Adrs1'])) {
                        $team->setAddress1($row['Dep Adrs1']);
                    }

                    if (!empty($row['Dep Adrs2'])) {
                        $team->setAddress2($row['Dep Adrs2']);
                    }

                    if (!empty($row['Dep Adrs3'])) {
                        $team->setAddress3($row['Dep Adrs3']);
                    }

                    if (!empty($row['Dep Postcode'])) {
                        $team->setAddressPostcode($row['Dep Postcode']);
                        $team->setAddressCountry('GB'); //postcode given means a UK address is given
                    }

                    $user->addTeam($team);
                    $this->em->persist($team);
                    $this->em->flush($team);
                }

                $this->userRepository->hardDeleteExistingUser($user);
                $this->em->persist($user);
                $this->em->flush($user);
                $this->added['users'][] = $row['Email'];
            }
        } 

        // update team name, if not set
        // can be removed if there is not need to update PA names after DDPB-1718
        // is released and one PA CSV upload is done
        if ($user->getTeams()->count()
            && ($team = $user->getTeams()->first())
            && $team->getTeamName() != $row['Dep Surname']
        ) {
            $team->setTeamName($row['Dep Surname']);
            $this->warnings[] = 'PA team ' . $team->getId() . ' updated to ' . $row['Dep Surname'];
            $this->em->flush($team);
        }


        return $user;
    }

    /**
     * @param array $row
     * @param EntityDir\User $user
     *
     * @return EntityDir\Client
     */
    private function upsertClient(array $row, EntityDir\User $user)
    {
        // find or create client
        $caseNumber = strtolower($row['Case']);
        $client = $this->clientRepository->findOneBy(['caseNumber' => $caseNumber]);
        if ($client) {
            foreach ($client->getUsers() as $cu) {
                $client->getUsers()->removeElement($cu);
            }
        } else {
            $this->log('Creating client');
            $client = new EntityDir\Client();
            $client
                ->setCaseNumber($caseNumber)
                ->setFirstname(trim($row['Forename']))
                ->setLastname(trim($row['Surname']));

            if (!empty($row['Client Adrs1'])) {
                $client->setAddress($row['Client Adrs1']);
            }

            if (!empty($row['Client Adrs2'])) {
                $client->setAddress2($row['Client Adrs2']);
            }

            if (!empty($row['Client Adrs3'])) {
                $client->setCounty($row['Client Adrs3']);
            }

            if (!empty($row['Client Postcode'])) {
                $client->setPostcode($row['Client Postcode']);
                $client->setCountry('GB'); //postcode given means a UK address is given
            }

            if (!empty($row['Client Phone'])) {
                $client->setPhone($row['Client Phone']);
            }

            if (!empty($row['Client Email'])) {
                $client->setEmail($row['Client Email']);
            }

            if (!empty($row['Client Date of Birth'])) {
                $client->setDateOfBirth(self::parseDate($row['Client Date of Birth'], '19') ?: null);
            }

            $this->added['clients'][] = $client->getCaseNumber();
            $this->em->persist($client);
        }

        //Add client to user
        $user->addClient($client);

        //Also add client to team members
        foreach ($user->getTeams() as $team) {
            foreach ($team->getMembers() as $member) {
                if ($member->getId() != $user->getId()) {
                    $member->addClient($client);
                }
            }
        }

        $this->em->flush($client);

        return $client;
    }

    /**
     * @param array $row
     * @param EntityDir\Client $client
     *
     * @return EntityDir\Report\Report
     */
    private function upsertReport(array $row, EntityDir\Client $client, EntityDir\User $user)
    {
        // find or create reports
        $reportEndDate = self::parseDate($row['Last Report Day'], '20');
        if (!$reportEndDate) {
            throw new \RuntimeException("Cannot parse date {$row['Last Report Day']}");
        }
        $reportType = EntityDir\CasRec::getTypeBasedOnTypeofRepAndCorref($row['Typeofrep'], $row['Corref'], EntityDir\User::ROLE_PA);
        $report = $client->getReportByEndDate($reportEndDate);
        if ($report) {
            // change report type if it's not already set AND report is not yet submitted
            if ($report->getType() != $reportType && !$report->getSubmitted()) {
                $this->log('Changing report type');
                $report->setType($reportType);
                $this->em->persist($report);
                $this->em->flush();
            }
        } else {
            $this->log('Creating report');

            $reportStartDate = self::generateReportStartDateFromEndDate($reportEndDate);

            $report = new EntityDir\Report\Report($client, $reportType, $reportStartDate, $reportEndDate, true);
            $client->addReport($report);   //double link for testing reasons

            $this->added['reports'][] = $client->getCaseNumber() . '-' . $reportEndDate->format('Y-m-d');
            $this->em->persist($report);
            $this->em->flush();
        }


        return $report;
    }

    /**
     * Generates and returns the report start date from a given end date.
     * -365 days + 1 if note a leap day (otherwise we get 2nd March)
     *
     * @param \DateTime $reportEndDate
     *
     * @return \DateTime $reportStartDate
     */
    public static function generateReportStartDateFromEndDate(\DateTime $reportEndDate)
    {
        $reportStartDate = clone $reportEndDate;

        $isLeapDay = $reportStartDate->format('d-M') == '29-Feb';
        $reportStartDate->sub(new \DateInterval('P1Y')); // One year behind end date
        if (!$isLeapDay) {
            $reportStartDate->add(new \DateInterval('P1D')); // + 1 day
        }

        return $reportStartDate;
    }

    /**
     * create DateTime object based on '16-Dec-2014' formatted dates
     * '16-Dec-14' format is accepted too, although seem deprecated according to latest given CSV files
     *
     * @param string $dateString e.g. 16-Dec-2014
     * @param string $century e.g. 20/19 Prefix added to 2-digits year
     *
     * @return \DateTime|false
     */
    public static function parseDate($dateString, $century)
    {
        $sep = '-';
        //$errorMessage = "Can't recognise format for date $dateString. expected d-M-Y or d-M-y e.g. 05-MAR-2005 or 05-MAR-05";
        $pieces = explode($sep, $dateString);

        // prefix century if needed
        if (strlen($pieces[2]) === 2) {
            $pieces[2] = ((string)$century) . $pieces[2];
        }
        // check format is d-M-Y
        if ((int)$pieces[0] < 1 || (int)$pieces[0] > 31 || strlen($pieces[1]) !== 3 || strlen($pieces[2]) !== 4) {
            return false;
            //throw new \InvalidArgumentException($errorMessage);
        }

        $ret = \DateTime::createFromFormat('d-M-Y', implode($sep, $pieces));
        if (!$ret instanceof \DateTime) {
            return false;
            //throw new \InvalidArgumentException($errorMessage);
        }

        return $ret;
    }

    /**
     * @param $message
     */
    private function log($message)
    {
        $this->logger->debug(__CLASS__ . ':' . $message);
    }
}
