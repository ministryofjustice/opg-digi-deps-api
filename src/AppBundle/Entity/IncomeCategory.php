<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 *
 * @ORM\Table(name="income_category")
 * @ORM\Entity
 */
class IncomeCategory
{
    /**
     * @var int
     *
     * @JMS\Groups({"income"})
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Groups({"income"})
     * @JMS\Type("string")
     *
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @var string
     *
     * @JMS\Groups({"income"})
     * @JMS\Type("string")
     *
     * @ORM\Column(type="text")
     */
    private $code;

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
     *
     * @return IncomeCategory
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return IncomeCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return IncomeCategory
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     *
     * @return IncomeCategory
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }
}
