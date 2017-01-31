<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 *
 * @ORM\Table(name="expense_type")
 * @ORM\Entity
 */
class ExpenseType
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
     * @JMS\Groups({"expense"})
     * @JMS\Type("AppBundle\Entity\ExpenseCategory")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ExpenseCategory")
     * @ORM\JoinColumn(name="expense_category_id", referencedColumnName="id")
     */
    private $expenseCategory;

    /**
     * @var string
     * @JMS\Groups({"expense"})
     * @JMS\Type("string")
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @var string
     * @JMS\Groups({"expense"})
     * @JMS\Type("string")
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
     * @return ExpenseType
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpenseCategory()
    {
        return $this->expenseCategory;
    }

    /**
     * @param mixed $expenseCategory
     *
     * @return ExpenseType
     */
    public function setExpenseCategory($expenseCategory)
    {
        $this->expenseCategory = $expenseCategory;

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
     * @return ExpenseType
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
     * @return ExpenseType
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
     * @return ExpenseType
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }
}
