<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 *
 * @ORM\Table(name="income_type")
 * @ORM\Entity
 */
class IncomeType
{
    /**
     * @var int
     *
     * @JMS\Groups({"expense"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var \AppBundle\Entity\IncomeCategory
     *
     * @JMS\Groups({"income"})
     * @JMS\Type("AppBundle\Entity\IncomeCategory")
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\IncomeCategory")
     * @ORM\JoinColumn(name="income_category_id", referencedColumnName="id")
     */
    private $incomeCategory;

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
     * @return IncomeType
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIncomeCategory()
    {
        return $this->incomeCategory;
    }

    /**
     * @param mixed $incomeCategory
     * @return IncomeType
     */
    public function setIncomeCategory($incomeCategory)
    {
        $this->incomeCategory = $incomeCategory;

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
     * @return IncomeType
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
     * @return IncomeType
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
     * @return IncomeType
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }
}
