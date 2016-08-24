<?php

namespace AppBundle\Entity\Odr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="odr_visits_care")
 */
class VisitsCare
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_visits_care_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Odr
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Odr\Odr", inversedBy="visitsCare")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id")
     */
    private $odr;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column(name="plan_move_residence", type="string", length=4, nullable=true)
     */
    private $planMoveNewResidence;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column(name="plan_move_residence_details", type="text", nullable=true)
     */
    private $planMoveNewResidenceDetails;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column(name="do_you_live_with_client", type="string", length=4, nullable=true)
     */
    private $doYouLiveWithClient;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column(name="how_often_contact_client", type="text", nullable=true)
     */
    private $howOftenDoYouContactClient;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column( name="does_client_receive_paid_care", type="text", nullable=true)
     */
    private $doesClientReceivePaidCare;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column(name="how_is_care_funded", length=255, type="string", nullable=true)
     */
    private $howIsCareFunded;

    /**
     * @var type
     *
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column( name="who_is_doing_the_caring", type="text", nullable=true)
     */
    private $whoIsDoingTheCaring;

    /**
     * @var type
     *
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column( name="does_client_have_a_care_plan", type="string", length=4, nullable=true)
     */
    private $doesClientHaveACarePlan;

    /**
     * @var date
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"visits-care"})
     * @ORM\Column(name="when_was_care_plan_last_reviewed", type="date", nullable=true, options={ "default": null })
     */
    private $whenWasCarePlanLastReviewed;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOdr()
    {
        return $this->odr;
    }

    /**
     * @param mixed $odr
     */
    public function setOdr(Odr $odr)
    {
        $this->odr = $odr;
    }

    /**
     * @return string
     */
    public function getPlanMoveNewResidence()
    {
        return $this->planMoveNewResidence;
    }

    /**
     * @param string $planMoveNewResidence
     */
    public function setPlanMoveNewResidence($planMoveNewResidence)
    {
        $this->planMoveNewResidence = $planMoveNewResidence;
    }

    /**
     * @return string
     */
    public function getPlanMoveNewResidenceDetails()
    {
        return $this->planMoveNewResidenceDetails;
    }

    /**
     * @param string $planMoveNewResidenceDetails
     */
    public function setPlanMoveNewResidenceDetails($planMoveNewResidenceDetails)
    {
        $this->planMoveNewResidenceDetails = $planMoveNewResidenceDetails;
    }

    /**
     * @return string
     */
    public function getDoYouLiveWithClient()
    {
        return $this->doYouLiveWithClient;
    }

    /**
     * @param string $doYouLiveWithClient
     */
    public function setDoYouLiveWithClient($doYouLiveWithClient)
    {
        $this->doYouLiveWithClient = $doYouLiveWithClient;
    }

    /**
     * @return string
     */
    public function getHowOftenDoYouContactClient()
    {
        return $this->howOftenDoYouContactClient;
    }

    /**
     * @param string $howOftenDoYouContactClient
     */
    public function setHowOftenDoYouContactClient($howOftenDoYouContactClient)
    {
        $this->howOftenDoYouContactClient = $howOftenDoYouContactClient;
    }

    /**
     * @return string
     */
    public function getDoesClientReceivePaidCare()
    {
        return $this->doesClientReceivePaidCare;
    }

    /**
     * @param string $doesClientReceivePaidCare
     */
    public function setDoesClientReceivePaidCare($doesClientReceivePaidCare)
    {
        $this->doesClientReceivePaidCare = $doesClientReceivePaidCare;
    }

    /**
     * @return string
     */
    public function getHowIsCareFunded()
    {
        return $this->howIsCareFunded;
    }

    /**
     * @param string $howIsCareFunded
     */
    public function setHowIsCareFunded($howIsCareFunded)
    {
        $this->howIsCareFunded = $howIsCareFunded;
    }

    /**
     * @return type
     */
    public function getWhoIsDoingTheCaring()
    {
        return $this->whoIsDoingTheCaring;
    }

    /**
     * @param type $whoIsDoingTheCaring
     */
    public function setWhoIsDoingTheCaring($whoIsDoingTheCaring)
    {
        $this->whoIsDoingTheCaring = $whoIsDoingTheCaring;
    }

    /**
     * @return type
     */
    public function getDoesClientHaveACarePlan()
    {
        return $this->doesClientHaveACarePlan;
    }

    /**
     * @param type $doesClientHaveACarePlan
     */
    public function setDoesClientHaveACarePlan($doesClientHaveACarePlan)
    {
        $this->doesClientHaveACarePlan = $doesClientHaveACarePlan;
    }

    /**
     * @return date
     */
    public function getWhenWasCarePlanLastReviewed()
    {
        return $this->whenWasCarePlanLastReviewed;
    }

    /**
     * @param date $whenWasCarePlanLastReviewed
     */
    public function setWhenWasCarePlanLastReviewed($whenWasCarePlanLastReviewed)
    {
        $this->whenWasCarePlanLastReviewed = $whenWasCarePlanLastReviewed;
    }
}