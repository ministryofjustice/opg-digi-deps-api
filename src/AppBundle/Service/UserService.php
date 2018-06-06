<?php

namespace AppBundle\Service;

use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class UserService
{
    /** @var EntityRepository */
    private $userRepository;

    /** @var EntityRepository */
    private $teamRepository;

    /** @var EntityManager */
    private $em;

    /**
     * @var OrgService
     */
    private $orgService;

    public function __construct(
        EntityManager $em,
        OrgService $orgService
    ) {
        $this->userRepository = $em->getRepository(User::class);
        $this->teamRepository = $em->getRepository(Team::class);
        $this->em = $em;
        $this->orgService = $orgService;
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
        $this->exceptionIfEmailExist($userToAdd->getEmail());

        // generate org team name
        if ($loggedInUser->isOrgNamedDeputy() &&
            !empty($data['pa_team_name']) &&
            $this->getTeams()->isEmpty()
        ) {
            $this->getTeams()->first()->setTeamName($data['pa_team_name']);
        }

        if ($loggedInUser->isOrgNamedOrAdmin() && $userToAdd->isDeputyOrg()) {
            $this->orgService->copyTeamAndClientsFrom($loggedInUser, $userToAdd);
        }

        $userToAdd->setRegistrationDate(new \DateTime());

        $userToAdd->recreateRegistrationToken();

        $this->em->persist($userToAdd);
        $this->em->flush();
    }

    /**
     * Update a user. Checks that the email is not in use then persists the entity
     *
     * @param User $loggedInUser Original user for comparison checks
     * @param User $originalUser Original user for comparison checks
     * @param User $userToEdit   The user to edit
     */
    public function editUser(User $loggedInUser, User $originalUser, User $userToEdit)
    {
        if ($originalUser->getEmail() != $userToEdit->getEmail()) {
            $this->exceptionIfEmailExist($userToEdit->getEmail());
        }

//        if ($loggedInUser->isOrgNamedOrAdmin()) {
//            $this->orgService->addTeamAndClientsFrom($loggedInUser, $userToAdd, $data);
//        }

        $this->em->flush($userToEdit);
    }

    /**
     * @param $email
     */
    private function exceptionIfEmailExist($email)
    {
        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new \RuntimeException("User with email {$email} already exists.", 422);
        }
    }
}
