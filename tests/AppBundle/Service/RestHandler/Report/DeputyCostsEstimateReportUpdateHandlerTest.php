<?php

namespace Tests\AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\RestHandler\Report\DeputyCostsEstimateReportUpdateHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class DeputyCostsEstimateReportUpdateHandlerTest extends TestCase
{
    /** @var DeputyCostsEstimateReportUpdateHandler */
    private $sut;

    /** @var EntityManager | \PHPUnit_Framework_MockObject_MockObject */
    private $em;

    /** @var Report | \PHPUnit_Framework_MockObject_MockObject */
    private $report;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->report = $this->getMockBuilder(Report::class)
            ->setConstructorArgs([new Client, Report::TYPE_102, new \DateTime, new \DateTime])
            ->setMethods(['updateSectionsStatusCache'])
            ->getMock();

        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new DeputyCostsEstimateReportUpdateHandler($this->em);
    }

    public function testUpdatesHowCharged()
    {
        $data['prof_deputy_costs_estimate_how_charged'] = 'new-value';

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHowCharged', 'new-value');
    }

    public function testResetsAssessedAnswersWhenFixedCostIsSet()
    {
        $data['prof_deputy_costs_estimate_how_charged'] = 'fixed';

        $this->report
            ->setProfDeputyCostsEstimateHasMoreInfo('yes')
            ->setProfDeputyCostsEstimateMoreInfoDetails('more info')
            ->setProfDeputyEstimateCosts(new ArrayCollection([new ProfDeputyEstimateCost()]));

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHowCharged', 'fixed');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', null);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', null);
        $this->assertTrue($this->report->getProfDeputyEstimateCosts()->isEmpty());
    }

    public function testPreservesAssessedAnswersWhenAssessedCostIsSet()
    {
        $data['prof_deputy_costs_estimate_how_charged'] = 'assessed';

        $this->report
            ->setProfDeputyCostsEstimateHasMoreInfo('yes')
            ->setProfDeputyCostsEstimateMoreInfoDetails('more info')
            ->setProfDeputyEstimateCosts(new ArrayCollection([new ProfDeputyEstimateCost()]));

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHowCharged', 'assessed');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', 'yes');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', 'more info');
        $this->assertFalse($this->report->getProfDeputyEstimateCosts()->isEmpty());
    }

    /**
     * @dataProvider getInvalidCostEstimateInputs
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionUpdatingCostEstimatesWithInsufficientData($data)
    {
        $this->invokeHandler($data);
    }

    /**
     * @return array
     */
    public function getInvalidCostEstimateInputs()
    {
        return [
            [['prof_deputy_estimate_costs' => [['amount' => '21', 'has_more_details' => false, 'more_details' => null]]]],
            [['prof_deputy_estimate_costs' => [['prof_deputy_estimate_cost_type_id' => 'foo', 'has_more_details' => false, 'more_details' => null]]]],
            [['prof_deputy_estimate_costs' => [['prof_deputy_estimate_cost_type_id' => 'foo', 'amount' => '21', 'has_more_details' => true]]]],
            [['prof_deputy_estimate_costs' => [['prof_deputy_estimate_cost_type_id' => 'foo', 'amount' => '21', 'more_details' => 'info']]]],
        ];
    }

    public function testUpdatesExistingOrCreatesNewProfDeputyEstimateCost()
    {
        $existing = new ProfDeputyEstimateCost();
        $existing
            ->setReport($this->report)
            ->setProfDeputyEstimateCostTypeId('other')
            ->setAmount('22.99')
            ->setHasMoreDetails(true)
            ->setMoreDetails('extra-details');

        $this->report->addProfDeputyEstimateCost($existing);

        $data['prof_deputy_estimate_costs'] = [
            ['prof_deputy_estimate_cost_type_id' => 'contact-client', 'amount' => '30.32', 'has_more_details' => false, 'more_details' => null],
            ['prof_deputy_estimate_cost_type_id' => 'other', 'amount' => '33.98', 'has_more_details' => true, 'more_details' => 'updated-details']
        ];

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->ensureEachProfDeputyEstimateCostWillBePersisted(count($data['prof_deputy_estimate_costs']));
        $this->invokeHandler($data);

        $this->assertCount(2, $this->report->getProfDeputyEstimateCosts());
        $this->assertExistingProfDeputyEstimateCostIsUpdated();
        $this->assertNewProfDeputyEstimateCostIsCreated();
    }

    public function testUpdatesMoreInformation()
    {
        $data['prof_deputy_costs_estimate_has_more_info'] = 'yes';
        $data['prof_deputy_costs_estimate_more_info_details'] = 'more info';

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', 'yes');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', 'more info');
    }

    public function testRemovesMoreInfoDetailsWhenNoLongerHasMoreInfo()
    {
        $data['prof_deputy_costs_estimate_has_more_info'] = 'no';

        $this->report
            ->setProfDeputyCostsEstimateHasMoreInfo('yes')
            ->setProfDeputyCostsEstimateMoreInfoDetails('more info');

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', 'no');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', null);
    }

    public function testPreservesMoreInfoDetailsWhenHasMoreInfo()
    {
        $data['prof_deputy_costs_estimate_has_more_info'] = 'yes';
        $data['prof_deputy_costs_estimate_more_info_details'] = 'more info updated';

        $this->report
            ->setProfDeputyCostsEstimateHasMoreInfo('yes')
            ->setProfDeputyCostsEstimateMoreInfoDetails('more info');

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', 'yes');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', 'more info updated');
    }

    private function ensureSectionStatusCacheWillBeUpdated()
    {
        $this
            ->report
            ->expects($this->once())
            ->method('updateSectionsStatusCache')
            ->with([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);
    }

    /**
     * @param $count
     */
    private function ensureEachProfDeputyEstimateCostWillBePersisted($count)
    {
        $this
            ->em
            ->expects($this->exactly($count))
            ->method('persist');
    }

    /**
     * @param array $data
     */
    private function invokeHandler(array $data)
    {
        $this->sut->handle($this->report, $data);
    }

    /**
     * @param $field
     * @param $expected
     */
    private function assertReportFieldValueIsEqualTo($field, $expected)
    {
        $getter = sprintf('get%s', ucfirst($field));
        $this->assertEquals($expected, $this->report->$getter());
    }

    private function assertExistingProfDeputyEstimateCostIsUpdated()
    {
        $profDeputyEstimateCost = $this->report->getProfDeputyEstimateCostByTypeId('other');

        $this->assertSame($this->report, $profDeputyEstimateCost->getReport());
        $this->assertEquals('33.98', $profDeputyEstimateCost->getAmount());
        $this->assertEquals(true, $profDeputyEstimateCost->getHasMoreDetails());
        $this->assertEquals('updated-details', $profDeputyEstimateCost->getMoreDetails());
    }

    private function assertNewProfDeputyEstimateCostIsCreated()
    {
        $profDeputyEstimateCost = $this->report->getProfDeputyEstimateCostByTypeId('contact-client');
        $this->assertSame($this->report, $profDeputyEstimateCost->getReport());
        $this->assertEquals('30.32', $profDeputyEstimateCost->getAmount());
        $this->assertEquals(false, $profDeputyEstimateCost->getHasMoreDetails());
        $this->assertEquals(null, $profDeputyEstimateCost->getMoreDetails());
    }
}
