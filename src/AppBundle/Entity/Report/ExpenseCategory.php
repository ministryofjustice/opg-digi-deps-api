<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="expense_category")
 */
class ExpenseCategory
{

    public static $expenseCategoryKeys = [
        'accommodation' => false,
        'care_fees' => false,
        'holidays_and_trips' => false,
        'household_bills' => false,
        'personal_allowance' => false,
        'new_investments' => false,
        'travel_costs' => false,
    ];

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"report-expense-categories"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="report_expense_category_id_seq", allocationSize=1, initialValue=1)
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
     * @JMS\Groups({"report-expense-categories"})
     * @ORM\Column(name="type_id", type="string", nullable=false)
     */
    private $typeId;

    /**
     * @var string
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"report-expense-categories"})
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
     * @return IncomeBenefitType
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
     * @return IncomeBenefitType
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
