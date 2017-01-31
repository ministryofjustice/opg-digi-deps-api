<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 *
 * @ORM\Table(name="income")
 * @ORM\Entity
 */
class Income
{
    /**
     * @var int
     *
     * @JMS\Groups({"expense"})
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="expense_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({"expense"})
     * @JMS\Type("AppBundle\Entity\Report\Report")
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var \AppBundle\Entity\IncomeType
     *
     * @JMS\Groups({"income"})
     * @JMS\Type("AppBundle\Entity\IncomeType")
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\IncomeType")
     * @ORM\JoinColumn(name="income_type_id", referencedColumnName="id")
     */
    private $incomeType;

    /**
     * @var float
     *
     * @JMS\Groups({"income"})
     * @JMS\Type("double")
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     * @JMS\Groups({"income"})
     *
     * @ORM\Column(name="income_date", type="date", nullable=true)
     */
    private $incomeDate;

    /**
     * @var string
     *
     * @JMS\Groups({"income"})
     * @JMS\Type("string")
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Income
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param mixed $report
     *
     * @return Income
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIncomeType()
    {
        return $this->incomeType;
    }

    /**
     * @param mixed $incomeType
     *
     * @return Income
     */
    public function setIncomeType($incomeType)
    {
        $this->incomeType = $incomeType;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return Income
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getIncomeDate()
    {
        return $this->incomeDate;
    }

    /**
     * @param \DateTime $incomeDate
     *
     * @return Income
     */
    public function setIncomeDate($incomeDate)
    {
        $this->incomeDate = $incomeDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Income
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
