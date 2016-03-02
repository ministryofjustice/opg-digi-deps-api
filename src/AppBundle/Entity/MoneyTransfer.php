<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * MoneyTransfer
 *
 * @ORM\Table(name="money_transfer")
 * @ORM\Entity
 */
class MoneyTransfer
{

    /**
     * @var integer
     * @JMS\Groups({"transfers", "basic"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @JMS\Groups({"transfers", "basic"})
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var Account
     * @JMS\Groups({"transfers", "basic"})
     * @JMS\SerializedName("accountFrom")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Account")
     * @ORM\JoinColumn(name="from_account_id", referencedColumnName="id")
     */
    private $from;

    /**
     * @var Account
     * @JMS\Groups({"transfers", "basic"})
     * @JMS\SerializedName("accountTo")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Account")
     * @ORM\JoinColumn(name="to_account_id", referencedColumnName="id")
     */
    private $to;


    /**
     * @var Report
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="moneyTransfers")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return MoneyTransfer
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }


    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }


    /**
     * @return Account
     */
    public function getFrom()
    {
        return $this->from;
    }


    /**
     * @return Account
     */
    public function getTo()
    {
        return $this->to;
    }


    /**
     * 
     * @param Account $from
     * @return MoneyTransfer
     */
    public function setFrom(Account $from)
    {
        $this->from = $from;
        return $this;
    }


    /**
     * @param Account $to
     * @return MoneyTransfer
     */
    public function setTo(Account $to)
    {
        $this->to = $to;
        return $this;
    }
    
    public function getReport()
    {
        return $this->report;
    }


    public function setReport(Report $report)
    {
        $this->report = $report;
        return $this;
    }


}