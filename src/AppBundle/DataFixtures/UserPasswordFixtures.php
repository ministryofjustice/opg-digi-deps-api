<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordFixtures extends Fixture implements OrderedFixtureInterface
{
    private $encoder;
    private $config;

    public function __construct(UserPasswordEncoderInterface $encoder, $config)
    {
        $this->encoder = $encoder;
        $this->config = $config;
    }

    public function load(ObjectManager $manager)
    {
        // Set all user passwords
        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll('');

        foreach ($users as $user) {
            $user->setPassword($this->encoder->encodePassword($user, $this->config['account_password']));
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 11;
    }
}