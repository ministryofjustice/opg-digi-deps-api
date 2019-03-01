<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Service\CasrecService;
use AppBundle\Service\ReportService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CasrecServiceTest extends WebTestCase
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

    /**
     * @var CasrecService
     */
    private $object = null;

    /** @var CasrecService */
    private $sut;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $reportService;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $validator;

    /** @var mixed */
    private $result;

    /** @var array */
    private $input = [];

    public static function setUpBeforeClass()
    {
        self::$frameworkBundleClient = static::createClient(['environment' => 'test',
                                                             'debug'       => false,]);

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');

        self::$fixtures = new Fixtures(self::$em);
    }

    public function setup()
    {
        $this->logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->reportService = self::$frameworkBundleClient->getContainer()->get('opg_digideps.report_service');
        $this->validator = self::$frameworkBundleClient->getContainer()->get('validator');

        $this->object = new CasrecService(self::$em, $this->logger, $this->reportService, $this->validator);
        Fixtures::deleteReportsData(['document', 'casrec', 'deputy_case', 'report_submission', 'report', 'odr', 'dd_team', 'dd_user', 'client', 'report']);
        self::$em->clear();

        $this->entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMock(LoggerInterface::class);
        $this->reportService = $this->getMockBuilder(ReportService::class)->disableOriginalConstructor()->getMock();
        $this->validator = $this->getMock(ValidatorInterface::class);

        $this->initQueryObjectChaining();

        $this->sut = new CasrecService($this->entityManager, $this->logger, $this->reportService, $this->validator);
    }

    private function initQueryObjectChaining()
    {
        $query = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $this
            ->entityManager
            ->method('createQuery')
            ->willReturn($query);

        $query
            ->method('setParameter')
            ->willReturnSelf();
    }

    public function testAddBulkAndCsv()
    {
        $u1 = self::$fixtures->createUser()
            ->setDeputyNo('DN1')
            ->setRegistrationDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'))
            ->setLastLoggedIn(\DateTime::createFromFormat('d/m/Y', '02/11/2017'))
        ;
        // create Client C! with two submitted report + one active report
        $c1 = self::$fixtures->createClient($u1)->setCaseNumber('1234567t');
        self::$fixtures->createReport($c1)->setSubmitted(true)->setSubmitDate(\DateTime::createFromFormat('d/m/Y', '05/06/2016'));
        self::$fixtures->createReport($c1)->setSubmitted(true)->setSubmitDate(\DateTime::createFromFormat('d/m/Y', '05/06/2017'));
        self::$fixtures->createReport($c1)->setSubmitted(false);
        self::$fixtures->createNdr($c1)->setSubmitted(true)->setSubmitDate(\DateTime::createFromFormat('d/m/Y', '04/06/2016'));
        self::$em->flush();
        self::$em->clear();

        // add two casrec entries, first of which matches, 2nd does not
        $ret = $this->object->addBulk([
            [
                'Case' => '1234567T',
                'Surname' => 'R1',
                'Deputy No' => 'DN1',
                'Dep Surname' => 'R2',
                'Dep Postcode' => 'SW1 aH3',
                'Typeofrep' => 'OPG102',
                'Corref' => 'L2',
                'custom1' => 'c1',
                'NDR' => 1,
            ],
            [
                'Case' => '22',
                'Surname' => 'H1',
                'Deputy No' => 'DN2',
                'Dep Surname' => 'H2',
                'Dep Postcode' => '',
                'Typeofrep' => 'OPG103',
                'Corref' => 'L3',
                'custom 2' => 'c2',
                'NDR' => '',
            ],

        ]);
        $this->assertEmpty($ret['errors'], print_r($ret, 1));
        $this->assertEquals(2, $ret['added'], print_r($ret, 1));

        self::$em->clear();
        $records = self::$em->getRepository(CasRec::class)->findBy([], ['id' => 'ASC']);

        $this->assertCount(2, $records);
        $record1 = $records[0]; /* @var $record1 CasRec */
        $record2 = $records[1]; /* @var $record1 CasRec */

        $this->assertEquals('r1', $record1->getClientLastname());
        $this->assertEquals('r2', $record1->getDeputySurname());
        $this->assertEquals('1', $record1->getColumn('NDR'));
        $this->assertEquals('sw1ah3', $record1->getDeputyPostCode());
        $this->assertEquals('opg102', $record1->getTypeOfReport());
        $this->assertEquals('l2', $record1->getCorref());

        // record1
        $casrecArray = $record1->toArray();
        $this->assertContains(date('d/m/Y'), $casrecArray['Uploaded at']);
        $this->assertContains(date('d/m/Y'), $casrecArray['Stats updated at']);
        $this->assertContains('01/11/2017', $casrecArray['Deputy registration date']);
        $this->assertContains('02/11/2017', $casrecArray['Deputy last logged in']);
        $this->assertEquals(2, $casrecArray['Reports submitted']);
        $this->assertContains('05/06/2017', $casrecArray['Last report submitted at']);
        $this->assertContains('04/06/2016', $casrecArray['NDR submitted at']);
        $this->assertEquals(1, $casrecArray['Reports active']);
        $this->assertContains('c1', $casrecArray['custom1']); // custom data is kepy
        $this->assertContains('DN1', $casrecArray['Deputy No']);
        $this->assertContains('1234567T', $casrecArray['Case']);
        $this->assertEquals('1', $casrecArray['NDR']);

        // record 2 (no match at all in the DB)
        $casrecArray = $record2->toArray();
        $this->assertContains(date('d/m/Y'), $casrecArray['Uploaded at']);
        $this->assertContains(date('d/m/Y'), $casrecArray['Stats updated at']);
        $this->assertContains('n.a.', $casrecArray['Deputy registration date']);
        $this->assertContains('n.a.', $casrecArray['Deputy last logged in']);
        $this->assertEquals('n.a.', $casrecArray['Reports submitted']);
        $this->assertEquals('n.a.', $casrecArray['Last report submitted at']);
        $this->assertEquals('n.a.', $casrecArray['NDR submitted at']);
        $this->assertEquals('n.a.', $casrecArray['Reports active']);
        $this->assertEquals('', $casrecArray['NDR']);

        // test CSV
        $file ='/tmp/dd_stats.unittest.csv';
        $this->object->saveCsv($file);
        $this->assertCount(3, file($file));
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAddBulkThrowsExceptionIfGivenEmptyData()
    {
        $this->ensureInputIsEmpty();
        $this->invokeAddBulkTest();
    }

    private function ensureInputIsEmpty()
    {
        $this->input = [];
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAddBulkThrowsExceptionIfGivenRowCountExceedsMaxCount()
    {
        $this->ensureInputContainsMoreThanAllowed();
        $this->invokeAddBulkTest();
    }

    private function ensureInputContainsMoreThanAllowed()
    {
        for ($i = 1; $i <= CasrecService::MAX_RECORDS_ALLOWED_IN_BULK +1; $i++) {
            $this->input[] = $i;
        }
    }

    public function testAddBulkCatchesExceptionsEncounteredOnAnInputEntryAndExitsGracefully()
    {
        $this->ensureInputContains(['alpha-row'], ['beta-row'], ['charlie-row']);
        $this->ensureExceptionWillBeThrownAtInputEntry(2);
        $this->invokeAddBulkTest();
        $this->assertReturnValueContainsSummaryDataEqualTo(['added' => 1, 'errors' => 1]);
    }

    private function ensureExceptionWillBeThrownAtInputEntry($throwAt)
    {
        $this
            ->validator
            ->expects($this->at($throwAt - 1))
            ->method('validate')
            ->willThrowException(new \Exception('error'));
    }

    public function testAddBulkCreatesAndPersistsAnewEntityForEachValidInputEntry()
    {
        $this->ensureInputContains(['invalid-row'], ['valid-row']);
        $this->assertEntitiesWillOnlyBeCreatedFromValidRows();
        $this->assertEachEntityWillBePersisted();
        $this->assertEntitesWillBeFlushedNtimes(1);
        $this->invokeAddBulkTest();
        $this->assertReturnValueContainsSummaryDataEqualTo(['added' => 1, 'errors' => 1]);
    }

    /**
     * @dataProvider getInputCountBoundaryVariations
     * @param $inputCount
     * @param $expectedFlushCount
     */
    public function testAddBulkFlushesEntitiesInBatches($inputCount, $expectedFlushCount)
    {
        $this->ensureInputContainsNentries($inputCount);
        $this->assertEntitesWillBeFlushedNtimes($expectedFlushCount);
        $this->invokeAddBulkTest();
        $this->assertReturnValueContainsSummaryDataEqualTo(['added' => $inputCount, 'errors' => 0]);
    }

    public function getInputCountBoundaryVariations()
    {
        return [
            ['inputCount' => 1, 'expectedFlushCount' => 1],
            ['inputCount' => CasrecService::PERSIST_EVERY, 'expectedFlushCount' => 2],
            ['inputCount' => CasrecService::PERSIST_EVERY + 1, 'expectedFlushCount' => 2],
            ['inputCount' => CasrecService::PERSIST_EVERY * 2 - 1, 'expectedFlushCount' => 2],
            ['inputCount' => CasrecService::PERSIST_EVERY * 2, 'expectedFlushCount' => 3],
        ];
    }

    private function ensureInputContains()
    {
        foreach (func_get_args() as $singleUploadItem) {
            $this->input[] = $singleUploadItem;
        }
    }

    /**
     * @param $numEntries
     */
    private function ensureInputContainsNentries($numEntries)
    {
        for ($i = 1; $i <= $numEntries; $i++) {
            $this->input[] = ['entry'];
        }
    }

    private function assertEntitiesWillOnlyBeCreatedFromValidRows()
    {
        $mockError = $this->getMockBuilder(ConstraintViolation::class)->disableOriginalConstructor()->getMock();

        $this
            ->validator
            ->expects($this->exactly(2))
            ->method('validate')
            ->willReturnOnConsecutiveCalls(
                new ConstraintViolationList([$mockError]),
                new ConstraintViolationList
            );

        $this
            ->entityManager
            ->expects($this->exactly(1))
            ->method('persist');
    }

    private function assertEachEntityWillBePersisted()
    {
        $this
            ->entityManager
            ->expects($this->exactly(1))
            ->method('persist');
    }

    private function assertEntitesWillBeFlushedNtimes($expectedTimes)
    {
        $this
            ->entityManager
            ->expects($this->exactly($expectedTimes))
            ->method('flush');
    }

    private function invokeAddBulkTest()
    {
        $this->result = $this->sut->addBulk($this->input);
    }

    public function assertReturnValueContainsSummaryDataEqualTo(array $expected)
    {
        $this->assertInternalType('array', $this->result);
        $this->assertEquals($expected['added'], $this->result['added']);
        $this->assertEquals($expected['errors'], count($this->result['errors']));
    }


    public function testSaveCsvConvertsEachRowInCasrecTableToCsvFormat()
    {

    }

    public function testSaveCsvAddsAcsvHeaderToFile()
    {

    }

    public function testSaveCsvRemovesExistingFile()
    {

    }

    public function testSaveCsvWritesFileToDirectory()
    {

    }

    public function testSaveCsvReturnsNumberOfLinesWritten()
    {

    }
}
