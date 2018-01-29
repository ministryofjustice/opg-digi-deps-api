<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\PaService;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaServiceTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected static $frameworkBundleClient;

    /**
     * @var EntityManager
     */
    protected static $em;

    /**
     * @var Fixtures
     */
    protected static $fixtures;


    public static $deputy1 = [
        'Deputy No'    => '1', //will get padded
        //'Pat Create'   => '12-Dec-02',
        //'Dship Create' => '28-Sep-07',
        'Dep Postcode' => 'N1 ABC',
        'Dep Forename' => 'Dep1',
        'Dep Surname'  => 'Uty2',
        'Dep Type'     => 23,
        'Dep Adrs1'    => 'ADD1',
        'Dep Adrs2'    => 'ADD2',
        'Dep Adrs3'    => 'ADD3',
        'Dep Adrs4'    => 'ADD4',
        'Dep Adrs5'    => 'ADD5',
        'Email'        => 'dep1@provider.com',
    ];

    public static $deputy2 = [
        'Deputy No'    => '00000002',
        'Dep Forename' => 'Dep2',
        'Dep Surname'  => 'Uty2',
        'Dep Type'     => 23,
        'Email'        => 'dep2@provider.com',
    ];

    public static $client1 = [
        'Case'       => '1111', //will get padded
        'Forename'   => 'Cly1',
        'Surname'    => 'Hent1',
        'Corref'     => 'L2',
        'Typeofrep'  => 'OPG102',
        'Last Report Day' => '16-Dec-2014',
        'Client Adrs1' => 'a1',
        'Client Adrs2' => 'a2',
        'Client Adrs3' => 'a3',
        'Client Postcode' => 'ap',
        'Client Phone' => 'caphone',
        'Client Email' => 'client@provider.com',
        'Client Date of Birth' => '05-Jan-47',
    ];


    public static $client2 = [
        'Case'       => '10002222',
        'Forename'   => 'Cly2',
        'Surname'    => 'Hent2',
        'Corref'     => 'L3',
        'Typeofrep'  => 'OPG103',
        'Last Report Day' => '04-Feb-2015',
    ];

    public static $client3 = [
        'Case'       => '1000000T',
        'Forename'   => 'Cly3',
        'Surname'    => 'Hent3',
        'Corref'     => 'L3G',
        'Typeofrep'  => 'OPG103',
        'Last Report Day' => '05-Feb-2015',
    ];


    /**
     * @var PaService
     */
    private $pa = null;

    public static function setUpBeforeClass()
    {
        self::$frameworkBundleClient = static::createClient(['environment' => 'test',
                                                             'debug'       => false,]);

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');
        self::$fixtures = new Fixtures(self::$em);
    }

    public function setup()
    {
        $logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->pa = new PaService(self::$em, $logger);
        Fixtures::deleteReportsData(['dd_user', 'client']);
        self::$em->clear();
    }

    public function testAddFromCasrecRows()
    {
        // create two clients for the same deputy, each one with a report
        $data = [
            // deputy 1 with client 1 and client 2
            0 => self::$deputy1 + self::$client1,
            1 => self::$deputy1 + self::$client2,
            // deputy 2 with client 3
            2 => self::$deputy2 + self::$client3,
        ];

        $ret1 = $this->pa->addFromCasrecRows($data);
        $this->assertEmpty($ret1['errors'], implode(',', $ret1['errors']));
        $this->assertEquals([
            'users'   => ['dep1@provider.com', 'dep2@provider.com'],
            'clients' => ['00001111', '1000000t', '10002222'],
            'reports' => ['00001111-2014-12-16',  '1000000t-2015-02-05', '10002222-2015-02-04'],
        ], $ret1['added']);
        // add again and check no override
        $ret2 = $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'users'   => [],
            'clients' => [],
            'reports' => [],
        ], $ret2['added']);
        self::$em->clear();

        //assert 1st deputy
        $user1 = self::$fixtures->findUserByEmail('dep1@provider.com');
        $this->assertInstanceof(EntityDir\User::class, $user1, 'deputy not added');
        $clients = $user1->getClients();
        $this->assertCount(2, $clients);
        $this->assertCount(1, $user1->getTeams());
        $this->assertSame('00000001', $user1->getDeputyNo());

        // assert 1st client and report
        $client1 = $user1->getClientByCaseNumber('00001111');
        $this->assertSame('00001111', $client1->getCaseNumber());
        $this->assertEquals('Cly1', $client1->getFirstname());
        $this->assertEquals('Hent1', $client1->getLastname());
        $this->assertEquals('a1', $client1->getAddress());
        $this->assertEquals('a2', $client1->getAddress2());
        $this->assertEquals('a3', $client1->getCounty());
        $this->assertEquals('ap', $client1->getPostcode());
        $this->assertEquals('client@provider.com', $client1->getEmail());
        $this->assertEquals('1947-01-05', $client1->getDateOfBirth()->format('Y-m-d'));
        $this->assertCount(1, $client1->getReports());
        $client1Report1 = $client1->getReports()->first();
        /* @var $client1Report1 EntityDir\Report\Report */
        $this->assertEquals('2013-12-17', $client1Report1->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2014-12-16', $client1Report1->getEndDate()->format('Y-m-d'));
        $this->assertEquals(EntityDir\Report\Report::TYPE_102_6, $client1Report1->getType());

        // assert 2nd client and report
        $client2 = $user1->getClientByCaseNumber('10002222');
        $this->assertEquals('Cly2', $client2->getFirstname());
        $this->assertEquals('Hent2', $client2->getLastname());
        $this->assertCount(1, $client2->getReports());
        $client2Report1 = $client2->getReports()->first();
        /* @var $client2Report1 EntityDir\Report\Report */
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_6, $client2Report1->getType());

        // assert 2nd deputy
        $user2 = self::$fixtures->findUserByEmail('dep2@provider.com');
        $clients = $user2->getClients();
        $this->assertCount(1, $clients);
        $this->assertCount(1, $user2->getTeams());

        // assert 1st client and report
        $client1 = $user2->getClientByCaseNumber('1000000t');
        $this->assertEquals('Cly3', $client1->getFirstname());
        $this->assertEquals('Hent3', $client1->getLastname());
        $this->assertCount(1, $client1->getReports());
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_6, $client1->getReports()->first()->getType());


        // check client 3 is associated with deputy2
        $this->assertCount(2, self::$fixtures->findUserByEmail('dep1@provider.com')->getClients());
        $this->assertCount(1, self::$fixtures->findUserByEmail('dep2@provider.com')->getClients());

        // move client2 from deputy1 -> deputy2
        $dataMove = [
            self::$deputy2 + self::$client2,
        ];
        $this->pa->addFromCasrecRows($dataMove);
        self::$em->clear();

        // check client 3 is now associated with deputy1
        $this->assertCount(1, self::$fixtures->findUserByEmail('dep1@provider.com')->getClients());
        $this->assertCount(2, self::$fixtures->findUserByEmail('dep2@provider.com')->getClients());

        // check that report type changes are applied
        $data[0]['Corref'] = 'L3G';
        $data[0]['Typeofrep'] = 'OPG103';
        $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'users'   => [],
            'clients' => [],
            'reports' => [],
        ], $ret2['added']);
        self::$em->clear();
        self::$em->clear();

        $user1 = self::$fixtures->findUserByEmail('dep1@provider.com');
        $this->assertInstanceof(EntityDir\User::class, $user1, 'deputy not added');
        $client1 = $user1->getClientByCaseNumber('00001111');
        $this->assertCount(1, $client1->getReports());
        $report = $client1->getReports()->first();
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_6, $report->getType());

    }

    /**
     * Data providor for reporting periods, end date and expected start date
     *
     * @return array
     */
    public function reportPeriodDateProvider()
    {
        return [
            ['2010-01-01', '2009-01-02'],
            ['2010-02-28', '2009-03-01'],
            ['2010-12-31', '2010-01-01'],
            ['2011-01-01', '2010-01-02'],
            ['2011-02-28', '2010-03-01'],
            ['2011-12-31', '2011-01-01'],
            ['2012-01-01', '2011-01-02'],
            ['2012-02-28', '2011-03-01'],
            ['2012-02-29', '2011-03-01'],
            ['2012-12-31', '2012-01-01'],
            ['2013-01-01', '2012-01-02'],
            ['2013-02-28', '2012-02-29'],
            ['2013-12-31', '2013-01-01'],
            ['2014-01-01', '2013-01-02'],
            ['2014-02-28', '2013-03-01'],
            ['2014-12-31', '2014-01-01'],
            ['2015-01-01', '2014-01-02'],
            ['2015-02-28', '2014-03-01'],
            ['2015-12-31', '2015-01-01'],
            ['2016-01-01', '2015-01-02'],
            ['2016-02-29', '2015-03-01'],
            ['2016-02-28', '2015-03-01'],
            ['2016-12-31', '2016-01-01'],
            ['2017-01-01', '2016-01-02'],
            ['2017-02-28', '2016-02-29'],
            ['2017-12-31', '2017-01-01']
        ];
    }

    /**
     * @dataProvider reportPeriodDateProvider
     *
     * @param $endDate
     * @param $expectedStartDate
     */
    public function testGenerateStartDateFromEndDate($endDate, $expectedStartDate)
    {
        $endDate = new \DateTime($endDate);
        $startDate = $this->pa->generateReportStartDateFromEndDate($endDate);

        $this->assertEquals($expectedStartDate, $startDate->format('Y-m-d'));
    }

    public function tearDown()
    {
        m::close();
    }
}
