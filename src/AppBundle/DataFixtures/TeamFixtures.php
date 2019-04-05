<?php
namespace AppBundle\DataFixtures;

use AppBundle\DataFixtures\UserFixtures;
use AppBundle\Entity\Client;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TeamFixtures extends Fixture implements OrderedFixtureInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Create teams
        $this->createTeam('PA', 'Public Authority', $manager);
        $this->createTeam('PROF', 'Professional', $manager);

        $manager->flush();
    }

    private function createTeam($code, $name, $manager) {
        // Create team
        $team = new Team($name . ' Team');
        $manager->persist($team);

        // Create team admin user
        $teamAdminUser = (new User())
            ->setFirstname($name)
            ->setLastname('Admin User')
            ->setEmail('behat-' . strtolower($code) . '-admin@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_' . $code . '_ADMIN');

        $teamAdminUser->setPassword($this->encoder->encodePassword($teamAdminUser, ''));
        $teamAdminUser->addTeam($team);
        $manager->persist($teamAdminUser);

        // Create team member user
        $teamMemberUser = (new User())
            ->setFirstname($name)
            ->setLastname('Team Member')
            ->setEmail('behat-' . strtolower($code) . '-team-member@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_' . $code . '_TEAM_MEMBER');

        $teamMemberUser->setPassword($this->encoder->encodePassword($teamMemberUser, ''));
        $teamMemberUser->addTeam($team);
        $manager->persist($teamMemberUser);

        $clientRepository = $manager->getRepository(Client::class);
        $clients = $clientRepository->findAll('');
        foreach ($clients as $client) {
            if ($client->getUsers()[0]->getRoleName() === 'ROLE_' . $code . '_NAMED') {
                $teamAdminUser->addClient($client);
                $teamMemberUser->addClient($client);
            }
        }
    }

    public function getOrder()
    {
        return 10;
    }
}