<?php

namespace Tests\AppBundle\v2\Registration\Uploader;

use AppBundle\Service\ReportService;
use AppBundle\v2\Registration\DeputyshipValidator;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use AppBundle\v2\Registration\Uploader\CasRecLayDeputyshipUploader;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class LayDeputyshipUploaderTest extends TestCase
{
    /** @var EntityManager | \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var DeputyshipValidator | \PHPUnit_Framework_MockObject_MockObject */
    protected $deputyshipValidator;

    /** @var ReportService | \PHPUnit_Framework_MockObject_MockObject */
    protected $reportService;

    /** @var CasRecFactory | \PHPUnit_Framework_MockObject_MockObject */
    private $factory;

    /** @var CasRecLayDeputyshipUploader */
    private $sut;

    /** {@inheritDoc} */
    protected function setUp()
    {
        $this->em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->deputyshipValidator = $this->getMockBuilder(DeputyshipValidator::class)->disableOriginalConstructor()->getMock();
        $this->reportService = $this->getMockBuilder(ReportService::class)->disableOriginalConstructor()->getMock();
        $this->factory = $this->getMockBuilder(CasRecFactory::class)->disableOriginalConstructor()->enableArgumentCloning()->getMock();

        $this->sut = new CasRecLayDeputyshipUploader(
            $this->em,
            $this->deputyshipValidator,
            $this->reportService,
            $this->factory
        );
    }

    /**
     * @test
     */
    public function foo()
    {
        $this->assertInstanceOf(CasRecLayDeputyshipUploader::class, $this->sut);
    }
}
