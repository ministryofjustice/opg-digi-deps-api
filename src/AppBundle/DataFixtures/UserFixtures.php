<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    private $userData = [
        [
            'id' => '103',
            'deputyType' => 'LAY',
            'reportType' => 'OPG103',
            'reportVariation' => 'L3',
        ],
        [
            'id' => '102',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
        ],
        [
            'id' => '104',
            'deputyType' => 'LAY',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4',
            'deputyType' => 'LAY',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG103',
            'reportVariation' => 'A3',
        ],
        [
            'id' => '102-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG102',
            'reportVariation' => 'A2',
        ],
        [
            'id' => '104-6',
            'deputyType' => 'PA',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG103',
            'reportVariation' => 'P3',
        ],
        [
            'id' => '102-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'P2',
        ],
        [
            'id' => '104-5',
            'deputyType' => 'PROF',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '-ndr',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
            'ndr' => true,
        ],
    ];

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Add users from array
        foreach ($this->userData as $data) {
            $this->addUser($data, $manager);
        }

        $manager->flush();
    }

    private function addUser($data, $manager) {
        // Create user
        $user = (new User())
            ->setFirstname(ucfirst($data['deputyType']) . ' Deputy ' . $data['id'])
            ->setLastname('User')
            ->setEmail('behat-' . strtolower($data['deputyType']) .  '-deputy' . $data['id'] . '@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(isset($data['ndr']))
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressCountry('GB')
            ->setRoleName($data['deputyType'] === 'LAY' ? 'ROLE_LAY_DEPUTY' : 'ROLE_' . $data['deputyType'] . '_NAMED');

        $user->setPassword($this->encoder->encodePassword($user, 'Abcd1234'));

        $manager->persist($user);

        // Create client
        $client = new Client();
        $client
            ->setCaseNumber('10101010')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setPhone('022222222222222')
            ->setAddress('Victoria road')
            ->setCourtDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'));

        $manager->persist($client);
        $user->addClient($client);

        if (!$client->getNdr()) {
            $ndr = new Ndr($client);
            $manager->persist($ndr);
        }

        // Create report
        $type = CasRec::getTypeBasedOnTypeofRepAndCorref($data['reportType'], $data['reportVariation'], $user->getRoleName());
        $startDate = $client->getExpectedReportStartDate();
        $endDate = $client->getExpectedReportEndDate();

        $report = new Report($client, $type, $startDate, $endDate);

        $manager->persist($report);
    }
}
