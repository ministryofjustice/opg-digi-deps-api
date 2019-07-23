<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity as EntityDir;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Report\Asset;
use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Report;

use AppBundle\Service\ReportService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use MockeryStub as m;

class ReportServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityDir\User
     */
    private $user;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var ReportService
     */
    protected $sut;

    public function setUp()
    {
        $this->user = new EntityDir\User();
        $client = new EntityDir\Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $this->bank1 = (new BankAccount())->setAccountNumber('1234');
        $this->asset1 = (new EntityDir\Report\AssetProperty())->setAddress('SW1');
        $this->report = new Report($client, Report::TYPE_102, new \DateTime('2015-01-01'), new \DateTime('2015-12-31'));
        $this->report
            ->setNoAssetToAdd(false)
            ->addAsset($this->asset1)
            ->addAccount($this->bank1)
            ->setSubmittedBy($this->user);

        $this->document1 = (new EntityDir\Report\Document($this->report))->setFileName('file1.pdf');
        $this->report->addDocument($this->document1);

        // mock em
        $this->reportRepo = m::mock(EntityDir\Repository\ReportRepository::class);
        $this->casrecRepo = m::mock(EntityRepository::class);
        $this->assetRepo = m::mock();
        $this->bankAccount = m::mock();

        $this->em = m::mock(EntityManager::class);

        $this->em->shouldReceive('getRepository')->andReturnUsing(function ($arg) use ($client) {
            switch ($arg) {
                case CasRec::class:
                    return  m::mock(EntityRepository::class)->shouldReceive('findOneBy')
                        ->with(['caseNumber' => $client->getCaseNumber()])
                        ->andReturn(null)
                        ->getMock();

                case Report::class:
                    return m::mock(EntityDir\Repository\ReportRepository::class);

                case Asset::class:
                    return m::mock(EntityRepository::class);

                case BankAccount::class:
                    return m::mock(BankAccount::class);
                default:
                    throw new \Exception("getRepository($arg) not mocked");
            }
        });

        $this->sut = new ReportService($this->em);
    }

    public function testSubmitInvalid()
    {
        $this->report->setAgreedBehalfDeputy(false);
        $this->setExpectedException(\RuntimeException::class, 'agreed');
        $this->sut->submit($this->report, $this->user, new \DateTime('2016-01-15'));
    }

    public function testSubmitValid()
    {
        $report = $this->report;

        // Create partial mock of ReportService
        $reportService = \Mockery::mock(ReportService::class, [$this->em])->makePartial();

        // mocks
        $this->em->shouldReceive('detach');
        $this->em->shouldReceive('flush');
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof Report;
        }));
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof EntityDir\Report\ReportSubmission;
        }));

        // clonePersistentResources should be called
        $reportService->shouldReceive('clonePersistentResources')->with(\Mockery::type(Report::class), $report);

        $report->setAgreedBehalfDeputy(true);
        $newYearReport = $reportService->submit($report, $this->user, new \DateTime('2016-01-15'));

        // assert current report
        $this->assertTrue($report->getSubmitted());

        // assert reportsubmissions
        $submission = $report->getReportSubmissions()->first();
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($report->getSubmittedBy(), $submission->getCreatedBy());

        //assert new year report
        $this->assertEquals($report->getType(), $newYearReport->getType());
        $this->assertEquals('2016-01-01', $newYearReport->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2016-12-31', $newYearReport->getEndDate()->format('Y-m-d'));
    }

    public function testResubmit()
    {
        $report = $this->report;
        $report->setUnSubmitDate(new \DateTime('2018-02-14'));

        // A report for the next report period should already exist
        $client = $this->report->getClient();
        $nextReport = new Report($client, Report::TYPE_102, new \DateTime('2016-01-01'), new \DateTime('2016-12-31'));
        $client->addReport($nextReport);

        // Create partial mock of ReportService
        $reportService = \Mockery::mock(ReportService::class, [$this->em])->makePartial();

        // mocks
        $this->em->shouldReceive('detach');
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof EntityDir\Report\ReportSubmission;
        }));
        $this->em->shouldReceive('flush')->with()->once(); //last in createNextYearReport

        // clonePersistentResources should be called
        $reportService->shouldReceive('clonePersistentResources')->with($nextReport, $report);

        $report->setAgreedBehalfDeputy(true);
        $newYearReport = $reportService->submit($report, $this->user, new \DateTime('2016-01-15'));

        // assert current report
        $this->assertTrue($report->getSubmitted());
        $this->assertNull($report->getUnSubmitDate());
        $this->assertNull($report->getUnsubmittedSectionsList());

        // assert reportsubmissions
        $submission = $report->getReportSubmissions()->first();
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($report->getSubmittedBy(), $submission->getCreatedBy());

        //assert new year report
        $this->assertEquals($newYearReport, $nextReport);
    }

    public function testPersistentResourcesCloned()
    {
        $client = $this->report->getClient();
        $newReport = new Report($client, Report::TYPE_102, new \DateTime('2016-01-01'), new \DateTime('2016-12-31'));

        // Assert asset is cloned
        $this->em->shouldReceive('detach')->once();
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($asset) {
            return $asset instanceof EntityDir\Report\AssetProperty
                && $asset->getAddress() === 'SW1';
        }))->once();

        // Assert bank account is cloned, with opening/closing balance modified
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($bankAccount) {
            return $bankAccount instanceof EntityDir\Report\BankAccount
                && $bankAccount->getAccountNumber() === '1234'
                && $bankAccount->getOpeningBalance() === $this->report->getBankAccounts()[0]->getClosingBalance()
                && is_null($bankAccount->getClosingBalance());
        }))->once();

        $this->em->shouldReceive('flush');

        $this->sut->clonePersistentResources($newReport, $this->report);
    }

    public function testSubmitAdditionalDocuments()
    {
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof EntityDir\Report\ReportSubmission;
        }));
        $this->em->shouldReceive('flush')->with()->once();

        $this->assertEmpty($this->report->getReportSubmissions());
        $currentReport = $this->sut->submitAdditionalDocuments($this->report, $this->user, new \DateTime('2016-01-15'));
        $submission = $currentReport->getReportSubmissions()->first();

        $this->assertContains($submission, $this->report->getReportSubmissions());
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($this->report->getSubmittedBy(), $submission->getCreatedBy());
    }


    public function testIsDue()
    {
        $this->assertEquals(false, ReportService::isDue(null));


        $todayMidnight = new \DateTime('today midnight');

        $oneMinuteBeforeLastMidnight = clone $todayMidnight;
        $oneMinuteBeforeLastMidnight->modify('-1 minute');

        $oneMinuteAfterLastMidnight = clone $todayMidnight;
        $oneMinuteAfterLastMidnight->modify('+1 minute');


        // end date is past (before midnight) => due
        $this->assertEquals(true, ReportService::isDue(new \DateTime('last week')));
        $this->assertEquals(true, ReportService::isDue($oneMinuteBeforeLastMidnight));

        // otherwise not due
        $this->assertEquals(false, ReportService::isDue($oneMinuteAfterLastMidnight));
        $this->assertEquals(false, ReportService::isDue(new \DateTime('next week')));
    }

    public function tearDown()
    {
        m::close();
    }
}
