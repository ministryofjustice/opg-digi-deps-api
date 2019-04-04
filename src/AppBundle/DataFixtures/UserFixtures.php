<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __constuct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = (new User())
            ->setFirstname('Greg')
            ->setLastname('Tyler')
            ->setEmail('greg.tyler@digital.justice.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressCountry('GB')
            ->setRoleName('ROLE_LAY_DEPUTY');

        $user->setPassword($this->encoder->encodePassword($user, 'Abcd1234'));
        
        $manager->persist($product);

        $manager->flush();
    }
}
