<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="report_type")
 */
class ReportType
{
    /**
     * Added via digideps:fixtures command.
     *
     * @JMS\Exclude
     */
    public static $fixtures = [
        // id | name | order
        ['102', 'Property and Financial', '10'],
        ['103', 'Property and Financial - short', '20'],
        ['104', 'Health and Welfare', '30'],
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="string", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="integer", nullable=true)
     */
    private $displayOrder;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ReportType
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $name
     * @return ReportType
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param int $displayOrder
     * @return ReportType
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }
}
