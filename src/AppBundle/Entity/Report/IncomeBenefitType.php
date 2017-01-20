<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="income_benefit_type")
 */
class IncomeBenefitType
{

    public static $benefitTypesKeys = [
        'employment_support_allowance_benefit' => false,
        'income_support_pension_guarantee_credit' => false,
        'income_related_employment_support_allowance' => false,
        'income_based_job_seeker_allowance' => false,
        'housing_benefit' => false,
        'severe_disablement_allowance' => false,
        'disability_living_allowance' => false,
        'attendance_allowance' => false,
        'state_pension' => false,
        'personal_independence_payment' => false,
        'universal_credit' => false,
    ];

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"report-income-benefit-types"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="report_income_benefit_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var string
     * @JMS\Groups({"report-income-benefit-types"})
     * @ORM\Column(name="type_id", type="string", nullable=false)
     */
    private $typeId;

    /**
     * @var string
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"report-income-benefit-types"})
     * @ORM\Column(name="present", type="boolean", nullable=true)
     */
    private $present;

    /**
     * Debt constructor.
     *
     * @param Report    $report
     * @param string $typeId
     */
    public function __construct(Report $report, $typeId)
    {
        $this->report = $report;
        $this->typeId = $typeId;
        $this->present = null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     *
     * @return IncomeBenefitTypes
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * @param string $present
     *
     * @return IncomeBenefitTypes
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

}
