<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TeamFixtures extends Fixture
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
    }
}
