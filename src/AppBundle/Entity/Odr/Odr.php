<?php

namespace AppBundle\Entity\Odr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Client;

/**
 * @ORM\Entity
 * @ORM\Table(name="odr")
 */
class Odr
{
    const PROPERTY_AND_AFFAIRS = 2;

    /**
     * @var int
     *
     * @JMS\Groups({"odr"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Client
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Client", inversedBy="odr")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @JMS\Groups({"odr"})
     * @JMS\Type("AppBundle\Entity\Odr\VisitsCare")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Odr\VisitsCare", mappedBy="odr", cascade={"persist"})
     **/
    private $visitsCare;

    /**
     * @var \Date
     *
     * @JMS\Groups({"odr"})
     * @JMS\Accessor(getter="getStartDate")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"odr"})
     * @JMS\Accessor(getter="getEndDate")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"odr"})
     * @JMS\Accessor(getter="getSubmitDate")
     * @JMS\Type("DateTime")
     * @ORM\Column(name="submit_date", type="datetime", nullable=true)
     */
    private $submitDate;

    /**
     * Odr constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
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
     * @return int
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param int $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getVisitsCare()
    {
        return $this->visitsCare;
    }

    /**
     * @param mixed $visitsCare
     */
    public function setVisitsCare($visitsCare)
    {
        $this->visitsCare = $visitsCare;
    }

    /**
     * @return \Date
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \Date $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @param \DateTime $submitDate
     */
    public function setSubmitDate($submitDate)
    {
        $this->submitDate = $submitDate;
    }


}
