<?php

namespace AppBundle\Service;

use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class UserService
{
    /** @var EntityRepository */
    protected $userRepository;

    /** @var EntityRepository */
    protected $teamRepository;

    /** @var EntityManager */
    protected $_em;

    public function __construct(
        UserRepository $userRepository,
        TeamRepository $teamRepository,
        EntityManager $em
    ) {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
        $this->_em = $em;
    }

    /**
     * Adds a new user to the database
     *
     * @param User $loggedInUser
     * @param User $userToAdd
     * @param $data
     */
    public function addUser(User $loggedInUser, User $userToAdd, $data)
    {
        $this->checkUserEmail($userToAdd);

        if ($loggedInUser->isOrgNamedDeputy() || $loggedInUser->isOrgAdministrator()) {
            $this->addPaUser($loggedInUser, $userToAdd, $data);
        }

        $userToAdd->setRegistrationDate(new \DateTime());

        $userToAdd->recreateRegistrationToken();

        $this->userRepository->hardDeleteExistingUser($userToAdd);

        $this->_em->persist($userToAdd);
        $this->_em->flush();
    }

    /**
     * Adds a new pa user to the database
     *
     * @param User $loggedInUser
     * @param User $userToAdd
     * @param $data
     */
    private function addPaUser(User $loggedInUser, User $userToAdd, $data)
    {
        $userToAdd->ensureRoleNameSet();
        $userToAdd->generateOrgTeam($loggedInUser, $data);

        /** duplicagtes line above? */
        if ($loggedInUser->hasRoleOrgNamed() &&
            !empty($data['pa_team_name']) &&
            $userToAdd->getTeams()->isEmpty()
        ) {
            $team = $userToAdd->getTeams()->first()->setTeamName($data['pa_team_name']);
            $this->_em->flush($team);
        }

        if ($userToAdd->isDeputyOrg()) {
            // add to creator's team
            if ($team = $loggedInUser->getTeams()->first()) {
                $userToAdd->addTeam($team);
                $this->_em->flush($team);
            }

            //copy clients
            foreach ($loggedInUser->getClients() as $client) {
                $userToAdd->addClient($client);
            }
        }
    }

    /**
     * Update a user. Checks that the email is not in use then persists the entity
     *
     * @param User $originalUser Original user for comparison checks
     * @param User $userToEdit   The user to edit
     */
    public function editUser(User $originalUser, User $userToEdit)
    {
        if (empty($userToEdit->getRoleName())) {
            if ($userToEdit->isProfDeputy()) {
                $userToEdit->setRoleName(User::ROLE_PROF_TEAM_MEMBER);
            } elseif ($userToEdit->isPaDeputy()) {
                $userToEdit->setRoleName(User::ROLE_PA_TEAM_MEMBER);
            }
        }

        if ($originalUser->getEmail() != $userToEdit->getEmail()) {
            $this->checkUserEmail($userToEdit);
            $this->userRepository->hardDeleteExistingUser($userToEdit);
        }

        $this->_em->flush($userToEdit);
    }

    public function editPaUser(User $originalUser, User $userToEdit)
    {
        if (empty($userToEdit->getRoleName())) {
            $userToEdit->setRoleName(User::ROLE_PA_TEAM_MEMBER);
        }

        $this->editUser($originalUser, $userToEdit);
    }

    /**
     * @param User $user
     */
    private function checkUserEmail(User $user)
    {
        if ($this->userRepository->findOneBy(['email' => $user->getEmail()])) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.", 422);
        }
    }
}
