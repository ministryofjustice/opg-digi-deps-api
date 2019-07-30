<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class OrgService
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

    private $debug = false;

    /**
     * @param EntityManager   $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->userRepository = $em->getRepository(EntityDir\User::class);
        $this->reportRepository = $em->getRepository(EntityDir\Report\Report::class);
        $this->clientRepository = $em->getRepository(EntityDir\Client::class);
        $this->organisationRepository = $em->getRepository(EntityDir\Organisation::class);
        $this->addressRepository = $em->getRepository(EntityDir\Address::class);
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

        $this->added = ['prof_users' => [],'pa_users' => [], 'clients' => [], 'reports' => []];
        $errors = [];
        foreach ($data as $index => $row) {
            $row = array_map('trim', $row);
            try {
                $userOrgNamed = $this->upsertOrgNamedUserFromCsv($row);
                if ($userOrgNamed instanceof EntityDir\User) {

                    $client = $this->upsertClientFromCsv($row, $userOrgNamed);
                    if ($client instanceof EntityDir\Client) {
                        $this->upsertReportFromCsv($row, $client, $userOrgNamed);
                    } else {
                        throw new \RuntimeException('Client could not be identified or created');
                    }
                } else {
                    throw new \RuntimeException('Named deputy could not be identified or created');
                }

            } catch (\Throwable $e) {
                $message = 'Error for Case: ' . $row['Case'] . ' for Deputy No: ' . $row['Deputy No'] . ': ' . $e->getMessage();
                $errors[] = $message;
            }
        }

        sort($this->added['prof_users']);
        sort($this->added['pa_users']);
        sort($this->added['clients']);
        sort($this->added['reports']);

        return [
            'added'    => $this->added,
            'errors'   => $errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * @param array $csvRow
     *
     * @return EntityDir\User
     */
    private function upsertOrgNamedUserFromCsv(array $csvRow)
    {
        $depType = $csvRow['Dep Type'];

        $csvEmail = strtolower($csvRow['Email']);
        $deputyNo = EntityDir\User::padDeputyNumber($csvRow['Deputy No']);
        $this->log('Processing row:  deputy := deputy no: ' . $deputyNo . ', dep type: ' . $depType . ' with email ' . $csvEmail);
        if (!isset(EntityDir\User::$depTypeIdToUserRole[$depType])) {
            throw new \RuntimeException('Dep Type not recognised');
        }
        $roleName = EntityDir\User::$depTypeIdToUserRole[$depType];

        $user = $this->userRepository->findOneBy([
            'deputyNo' => $deputyNo,
            'roleName' => $roleName
        ]);

        // Notify email change
        if ($user && $user->getEmail() !== $csvEmail) {
            $this->warnings[$user->getDeputyNo()] = 'Deputy ' . $user->getDeputyNo() .
                ' has changed their email to ' . $user->getEmail() . '. ' .
                'Please update the CSV to reflect the new email address.<br />';
        }

        // create user if not existing
        if (!$user) {
            // check for duplicate email address

            $userWithSameEmail = $this->userRepository->findOneBy(['email' => $csvEmail]);
            if ($userWithSameEmail) {
                $this->log('Deputy email address already exists ');
                $this->warnings[] = 'Deputy ' . $deputyNo .
                    ' cannot be added with email ' . $csvEmail .
                    '. Email already taken by Deputy No: ' . $userWithSameEmail->getDeputyNo();
            } else {
                $this->log('Creating new deputy ' . $deputyNo);

                $user = new EntityDir\User();
                $user
                    ->setRegistrationDate(new \DateTime())
                    ->setDeputyNo($deputyNo)
                    ->setEmail($csvRow['Email'])
                    ->setFirstname($csvRow['Dep Forename'])
                    ->setLastname($csvRow['Dep Surname'])
                    ->setRoleName($roleName);

                // create team (if not already existing)
                if ($user->getTeams()->isEmpty()) {
                    $this->log('Creating new team: ' . $csvRow['Dep Surname']);

                    // Dep Surname in the CSV is actually the PA team name
                    $team = new EntityDir\Team($csvRow['Dep Surname']);
                    $user->addTeam($team);
                    $this->em->persist($team);
                    $this->em->flush($team);
                }

                if ($user->isProfDeputy()) {
                    $this->added['prof_users'][] = $csvRow['Email'];
                } elseif ($user->isPaDeputy()) {
                    $this->added['pa_users'][] = $csvRow['Email'];
                }

            }
        }

        // update user address, if not set
        // the following could be moved to line 154 if no update is needed (DDPB-2262)
        if ($user instanceof EntityDir\User && (!empty($csvRow['Dep Adrs1']) && !$user->getAddress1())) {
            $user
                ->setAddress1($csvRow['Dep Adrs1'])
                ->setAddress2($csvRow['Dep Adrs2'])
                ->setAddress3($csvRow['Dep Adrs3'])
                ->setAddressPostcode($csvRow['Dep Postcode'])
                ->setAddressCountry('GB')
            ;
        }

        // update team name, if not set
        // can be removed if there is not need to update PA names after DDPB-1718
        // is released and one PA CSV upload is done
        if ($user instanceof EntityDir\User && $user->getTeams()->count()
            && ($team = $user->getTeams()->first())
            && $team->getTeamName() != $csvRow['Dep Surname']
        ) {
            $team->setTeamName($csvRow['Dep Surname']);
            $this->warnings[] = 'Organisation/Team ' . $team->getId() . ' updated to ' . $csvRow['Dep Surname'];
            $this->em->flush($team);
        }
        if ($user instanceof EntityDir\User) {
            $this->em->persist($user);
            $this->em->flush($user);
        }

        return $user;
    }

    /**
     * @param array          $row          keys: Case, caseNumber, Forename, Surname, Client Adrs1...
     * @param EntityDir\User $userOrgNamed the user the client should belong to
     *
     * @return EntityDir\Client
     */
    private function upsertClientFromCsv(array $row, EntityDir\User $userOrgNamed)
    {
        // find or create client
        $caseNumber = EntityDir\Client::padCaseNumber(strtolower($row['Case']));

        /** @var EntityDir\Client $client */
        $client = $this->clientRepository->findOneBy(['caseNumber' => $caseNumber]);

        if ($client) {
            $this->log('FOUND client in database with id: ' . $client->getId());
            $client->setUsers(new ArrayCollection());
        } else {
            $organisation = $this->identifyOrgansation($row);

            $this->log('Creating client');
            $client = new EntityDir\Client();
            $client
                ->setCaseNumber($caseNumber)
                ->setFirstname(trim($row['Forename']))
                ->setLastname(trim($row['Surname']))
                ->setOrganisation($organisation);

            // set court date from Last report day
            $courtDate = new \DateTime($row['Last Report Day']);
            $client->setCourtDate($courtDate->modify('-1year +1day'));

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
                $client->setDateOfBirth(ReportUtils::parseCsvDate($row['Client Date of Birth'], '19') ?: null);
            }

            $this->added['clients'][] = $client->getCaseNumber();

        }
        $this->log('Setting named deputy on client to deputy id:' . $userOrgNamed->getId());
        $client->setNamedDeputy($userOrgNamed);

        // Add client to named user (will be done later anyway)
        $client->addUser($userOrgNamed);

        // Add client to all the team members of all teams the user belongs to
        // (duplicates are auto-skipped)

        $teams = $userOrgNamed->getTeams();
        $depCount = 0;
        foreach ($teams as $team) {
            $members = $team->getMembers();
            foreach ($members as $member) {
                $client->addUser($member);
                $depCount++;
            }
        }
        $this->log('Assigned ' . $depCount . ' additional deputies to client');

        $this->em->persist($client);

        $this->em->flush();

        return $client;
    }

    /**
     * @param array            $csvRow keys: Last Report Day, Typeofrep, }
     * @param EntityDir\Client $client the client the report should belong to
     * @param EntityDir\User   $user   the user (needed for determine the report type, dependendent on user role)
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return EntityDir\Report\Report
     */
    private function upsertReportFromCsv(array $csvRow, EntityDir\Client $client, EntityDir\User $user)
    {
        // find or create reports
        $reportEndDate = ReportUtils::parseCsvDate($csvRow['Last Report Day'], '20');
        if (!$reportEndDate) {
            throw new \RuntimeException("Cannot parse date {$csvRow['Last Report Day']}");
        }
        $reportType = EntityDir\CasRec::getTypeBasedOnTypeofRepAndCorref($csvRow['Typeofrep'], $csvRow['Corref'], $user->getRoleName());
        $report = $client->getCurrentReport();

        // already existing, just change type
        if ($report) {
            // change report type if it's not already set AND report is not yet submitted
            if ($report->getType() != $reportType && !$report->getSubmitted() && empty($report->getUnSubmitDate())) {
                $this->log('Changing report type from ' . $report->getType() . ' to ' . $reportType);
                $report->setType($reportType);
                $this->em->persist($report);
                $this->em->flush();
            }

            return $report;
        }

        $this->log('Creating new report');
        $reportStartDate = ReportUtils::generateReportStartDateFromEndDate($reportEndDate);
        $report = new EntityDir\Report\Report($client, $reportType, $reportStartDate, $reportEndDate, true);
        $client->addReport($report);   //double link for testing reasons
        $this->added['reports'][] = $client->getCaseNumber() . '-' . $reportEndDate->format('Y-m-d');
        $this->em->persist($report);
        $this->em->flush();
        $this->em->clear();
        return $report;
    }

    /**
     * @param EntityDir\User $userCreator
     * @param $id
     *
     * @throws AccessDeniedException if user not part of the team the creator user belongs to
     *
     * @return EntityDir\User|null|object
     *
     */
    public function getMemberById(EntityDir\User $userCreator, $id)
    {
        $user = $this->em->getRepository(EntityDir\User::class)->find($id);
        if (!array_key_exists($id, $userCreator->getMembersInAllTeams())) {
            throw new AccessDeniedException('User not part of the same team');
        }

        return $user;
    }

    /**
     * @param EntityDir\User $userWithTeams
     * @param EntityDir\User $userBeingAdded
     */
    public function addUserToUsersTeams(EntityDir\User $userWithTeams, EntityDir\User $userBeingAdded)
    {
        $teamIds = $this->em->getRepository('AppBundle\Entity\Team')->findAllTeamIdsByUser($userWithTeams);

        foreach ($teamIds as $teamId) {
            $this
                ->em
                ->getRepository('AppBundle\Entity\Client')
                ->saveUserToTeam($userBeingAdded, $teamId);
        }
    }

    /**
     * @param EntityDir\User $userWithClients
     * @param EntityDir\User $userBeingAdded
     */
    public function addUserToUsersClients(EntityDir\User $userWithClients, EntityDir\User $userBeingAdded)
    {
        $clientIds = $this->em->getRepository('AppBundle\Entity\Client')->findAllClientIdsByUser($userWithClients);

        foreach ($clientIds as $clientId) {
            $this
                ->em
                ->getRepository('AppBundle\Entity\Client')
                ->saveUserToClient($userBeingAdded, $clientId);
        }
    }

    /**
     * Delete $user from all the teams $loggedInUser belongs to
     * Also removes the user, if doesn't belong to any team any longer
     *
     * @param EntityDir\User $loggedInUser
     * @param EntityDir\User $user
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeUserFromTeamsOf(EntityDir\User $loggedInUser, EntityDir\User $user)
    {
        // remove user from teams the logged-user (operation performer) belongs to
        foreach ($loggedInUser->getTeams() as $team) {
            $user->getTeams()->removeElement($team);
        }

        // remove client that also belongs to the creator
        // (equivalent to remove client from all the teams of the creator)
        foreach ($loggedInUser->getClients() as $client) {
            $client->removeUser($user);
        }

        // remove user if belonging to no teams
        if (count($user->getTeams()) === 0) {
            $this->em->remove($user);
        }

        $this->em->flush();
    }

    /**
     * Returns the organisation populated with address
     *
     * @param $row
     *
     * @return EntityDir\Organisation
     */
    private function identifyOrgansation($row)
    {
        $organisation = $this->organisationRepository->findOneBy(['organisationName' => $row['Dep Adrs1']]);
        /** @var ArrayCollection $addresses */
        $address = $this->identifyAddress($row);

        if ($organisation instanceof EntityDir\Organisation) {
            $organisation->addAddress($address);
        } else {
            $organisation = new EntityDir\Organisation($row['Dep Adrs1'], $address);
        }

        return $organisation;
    }

    /**
     * @param array $row
     * @return EntityDir\Address
     */
    private function identifyAddress(array $row)
    {
        $address = $this->addressRepository->findOneBy(['deputyAddressNo' => $row['DepAddr No']]);
        if (!$address instanceof EntityDir\Address) {
            $addressData = $this->extractOrgAddressFromRow($row);
            $address = new EntityDir\Address($addressData);
        }

        return $address;
    }

    /**
     * @param array $row
     * @return array
     */
    private function extractOrgAddressFromRow(array $row)
    {
        return [
            'address1' => $row['Dep Adrs1'],
            'address2' => $row['Dep Adrs2'],
            'address3' => $row['Dep Adrs3'],
            'address4' => $row['Dep Adrs4'],
            'address5' => $row['Dep Adrs5'],
            'postcode' => $row['Dep Postcode'],
            'country' => 'gb'
        ];
    }

    /**
     * @param $message
     */
    private function log($message)
    {
        if ($this->debug) {
            $this->logger->warning(__CLASS__ . ':' . $message);
        }
    }
}
