<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * General Management Cost.
 *
 * @ORM\Table(name="prof_deputy_management_cost")
 * @ORM\Entity
 */
class ProfDeputyManagementCost
{
    /**
     * ProfDeputyManagementCost constructor.
     *
     * @param $profDeputyManagementCostTypeId
     * @param float $amount
     * @param string  $hasMoreDetails
     * @param string  $moreDetails
     */
    public function __construct($profDeputyManagementCostTypeId, $amount, $hasMoreDetails, $moreDetails)
    {
        $this->profDeputyManagementCostTypeId = $profDeputyManagementCostTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    /**
     * @var int
     *
     * @JMS\Groups({"prof-deputy-management-costs"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="prof_management_costs_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var float
     *
     * @JMS\Type("float")
     * @JMS\Groups({"prof-deputy-management-costs"})
     *
     * @ORM\Column(name="amount", type="float", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var string a value in self:$profDeputyManagementCostTypeIds
     *
     * @JMS\Groups({"prof-deputy-management-costs"})
     *
     * @ORM\Column(name="prof_deputy_management_cost_type_id", type="string", nullable=false)
     */
    private $profDeputyManagementCostTypeId;

    /**
     * @var bool
     *
     * @JMS\Groups({"prof-deputy-management-costs"})
     * @JMS\Type("boolean")
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    private $hasMoreDetails;

    /**
     * @var string
     *
     * @JMS\Groups({"prof-deputy-management-costs"})
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    private $moreDetails;

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyManagementCostTypeId()
    {
        return $this->profDeputyManagementCostTypeId;
    }

    /**
     * @param mixed $profDeputyManagementCostTypeId
     */
    public function setProfDeputyManagementCostTypeId($profDeputyManagementCostTypeId)
    {
        $this->profDeputyManagementCostTypeId = $profDeputyManagementCostTypeId;
    }

    /**
     * @return string
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param string $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
    }
}
