<?php

namespace AppBundle\Service;

use AppBundle\Entity\Ndr\Ndr;
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

        $userToAdd->setRegistrationDate(new \DateTime());
        $userToAdd->recreateRegistrationToken();
        $this->em->persist($userToAdd);
        $this->em->flush();

        $this->orgService->addUserToUsersClients($loggedInUser, $userToAdd);

        if ($loggedInUser->isOrgNamedOrAdmin() && $userToAdd->isDeputyOrg()) {
            $this->orgService->addUserToUsersTeams($loggedInUser, $userToAdd);
        }
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

        if ($userToEdit->isLayDeputy()) {
            $client = $userToEdit->getFirstClient();

            if ($userToEdit->getNdrEnabled() && null === $client->getNdr()) {
                $ndr = new Ndr($client);
                $this->em->persist($ndr);
                $this->em->flush($ndr, $client);
            }
        }

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
