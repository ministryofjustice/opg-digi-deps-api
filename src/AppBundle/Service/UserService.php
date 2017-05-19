<?php

namespace AppBundle\Service;

use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use AppBundle\Exception\NotFound;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\BankAccount as ReportBankAccount;

class UserService
{
    /** @var EntityRepository */
    protected $userRepository;

    /** @var EntityRepository */
    protected $teamRepository;

    public function __construct(
        UserRepository $userRepository,
        TeamRepository $teamRepository,
        EntityManager $em
    )
    {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
        $this->_em = $em;
    }

    public function addPaUser(User $loggedInUser, User $userToAdd, $data)
    {
        $userToAdd->ensureRoleNameSet();
        $userToAdd->generatePaTeam($loggedInUser, $data);

        if ($loggedInUser->isPaNamedDeputy() &&
            !empty($data['pa_team_name']) &&
            $userToAdd->getTeams()->isEmpty()
        ) {
            $team = $userToAdd->getTeams()->first()->setTeamName($data['pa_team_name']);
            $this->_em->flush($team);
        }

        $isPaMemberBeingCreated = in_array($userToAdd->getRoleName(), [User::ROLE_PA_ADMIN, User::ROLE_PA_TEAM_MEMBER]);
        if ($isPaMemberBeingCreated) {
            // add to creator's team
            if ($team = $loggedInUser->getTeams()->first()) {
                $userToAdd->addTeam($team);
                $this->_em->flush($team);
            }

            //copy clients
            foreach($loggedInUser->getClients() as $client) {

                $userToAdd->addClient($client);
            }
        }
    }

    public function editUser(User $originalUser, User $userToEdit)
    {
        if (empty($userToEdit->getRoleName())) {
            $userToEdit->setRoleName(User::ROLE_PA_TEAM_MEMBER);
        }

        if($originalUser->getEmail() != $userToEdit->getEmail()) {
            if ($this->userRepository->findOneBy(['email' => $userToEdit->getEmail()])) {
                throw new \RuntimeException("PA User with email {$userToEdit->getEmail()} already exists.", 422);
            }

            $existingSoftDeletedUser = $this->userRepository->findUnfilteredOneBy(['email' => $userToEdit->getEmail()]);
            if ($existingSoftDeletedUser != null) {
                // delete soft deleted user a second time to hard delete it
                $this->getEntityManager()->remove($existingSoftDeletedUser);
                $this->getEntityManager()->flush();
            }
        }
    }

    public function editPaUser(User $originalUser, User $userToEdit)
    {
        if (empty($userToEdit->getRoleName())) {
            $userToEdit->setRoleName(User::ROLE_PA_TEAM_MEMBER);
        }

        $this->editUser($originalUser, $userToEdit);
    }
}
