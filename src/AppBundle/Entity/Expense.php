<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 *
 * @ORM\Table(name="expense")
 * @ORM\Entity
 */
class Expense
{
    /**
     * @var int
     *
     * @JMS\Groups({"expense"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="expense_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({"expense"})
     * @JMS\Type("AppBundle\Entity\Report\Report")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @JMS\Groups({"expense"})
     * @JMS\Type("AppBundle\Entity\ExpenseType")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ExpenseType")
     * @ORM\JoinColumn(name="expense_type_id", referencedColumnName="id")
     */
    private $expenseType;

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
     * @JMS\Groups({"expense"})
     *
     * @ORM\Column(name="expense_date", type="date", nullable=true)
     */
    private $expenseDate;

    /**
     * @var string
     * @JMS\Groups({"expense"})
     * @JMS\Type("string")
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
     * @return Expense
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
     * @return Expense
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpenseType()
    {
        return $this->expenseType;
    }

    /**
     * @param mixed $expenseType
     *
     * @return Expense
     */
    public function setExpenseType($expenseType)
    {
        $this->expenseType = $expenseType;

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
     * @return Expense
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpenseDate()
    {
        return $this->expenseDate;
    }

    /**
     * @param \DateTime $expenseDate
     *
     * @return Expense
     */
    public function setExpenseDate($expenseDate)
    {
        $this->expenseDate = $expenseDate;

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
     * @return Expense
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}