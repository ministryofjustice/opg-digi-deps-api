<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminUserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Add admin users
        $adminUser = (new User())
            ->setFirstname('Admin')
            ->setLastname('User')
            ->setEmail('admin@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_ADMIN');

        $adUser = (new User())
            ->setFirstname('AD user')
            ->setLastname('ADsurname')
            ->setEmail('ad@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_AD');

        $caseManager = (new User())
            ->setFirstname('Case')
            ->setLastname('Manager')
            ->setEmail('casemanager@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_CASE_MANAGER');

        $adminUser->setPassword($this->encoder->encodePassword($adminUser, ''));
        $adUser->setPassword($this->encoder->encodePassword($adUser, ''));
        $caseManager->setPassword($this->encoder->encodePassword($caseManager, ''));
            
        $manager->persist($adminUser);
        $manager->persist($adUser);
        $manager->persist($caseManager);

        $manager->flush();
    }
}
