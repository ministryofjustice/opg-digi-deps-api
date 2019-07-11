<?php

namespace Tests\AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\User;
use AppBundle\Service\ReportService;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecCreationException;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use AppBundle\v2\Registration\Uploader\LayDeputyshipUploader;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class LayDeputyshipUploaderTest extends TestCase
{
    /** @var EntityManager | \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var ReportService | \PHPUnit_Framework_MockObject_MockObject */
    protected $reportService;

    /** @var CasRecFactory | \PHPUnit_Framework_MockObject_MockObject */
    private $factory;

    /** @var LayDeputyshipUploader */
    private $sut;

    /** {@inheritDoc} */
    protected function setUp()
    {
        $this->em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->reportService = $this->getMockBuilder(ReportService::class)->disableOriginalConstructor()->setMethods(['updateCurrentReportTypes'])->getMock();
        $this->factory = $this->getMockBuilder(CasRecFactory::class)->disableOriginalConstructor()->enableArgumentCloning()->getMock();

        $this->sut = new LayDeputyshipUploader(
            $this->em,
            $this->reportService,
            $this->factory
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function throwsExceptionIfDataSetTooLarge()
    {
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < LayDeputyshipUploader::MAX_UPLOAD + 1; $i++) {
            $collection->append(new LayDeputyshipDto());
        }

        $this->sut->upload($collection);
    }

    /**
     * @test
     */
    public function persistsAnEntryForEachValidDeputyship()
    {
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < 3; $i++) {
            $collection->append($this->buildLayDeputyshipDto($i));
        }

        $a = new CasRec([]);
        $b = new CasRec([]);
        $c = new CasRec([]);

        // Assert 3 CasRec entities will be created.
        $this->factory
            ->expects($this->exactly(3))
            ->method('createFromDto')
            ->willReturnOnConsecutiveCalls($a, $b, $c);

        // Assert Report Types will be sent for evaluation for each CasRec that is created.
        $this->reportService
            ->expects($this->once())
            ->method('updateCurrentReportTypes')
            ->with([$a, $b, $c], User::ROLE_LAY_DEPUTY);

        $return = $this->sut->upload($collection);

        $this->assertEquals(3, $return['added']);
        $this->assertCount(0, $return['errors']);
    }

    /**
     * @test
     */
    public function ignoresDeputyshipsWithInvalidDeputyshipData()
    {
        $collection = new LayDeputyshipDtoCollection();
        $collection->append($this->buildLayDeputyshipDto(1));

        // Ensure factory will throw an exception
        $this
            ->factory
            ->method('createFromDto')
            ->willThrowException(new CasRecCreationException('Unable to create CasRec entity'));

        $this->assertReportTypeWillNotBeSentForEvaluation();

        $return = $this->sut->upload($collection);

        $this->assertEquals(0, $return['added']);
        $this->assertCount(1, $return['errors']);
        $this->assertEquals('ERROR IN LINE 2: Unable to create CasRec entity', $return['errors'][0]);
    }

    /**
     * @param $count
     * @return LayDeputyshipDto
     */
    private function buildLayDeputyshipDto($count): LayDeputyshipDto
    {
        return (new LayDeputyshipDto())
            ->setCaseNumber('case-'.$count)
            ->setDeputyNumber('depnum-'.$count);
    }

    private function assertReportTypeWillNotBeSentForEvaluation(): void
    {
        $this->reportService
            ->expects($this->once())
            ->method('updateCurrentReportTypes')
            ->with([], User::ROLE_LAY_DEPUTY);
    }
}
