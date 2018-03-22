<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="money_transaction")
 * @ORM\Entity
 */
class MoneyTransaction implements MoneyTransactionInterface
{
    /**
     * Static list of possible money transaction categories
     *
     * hasMoreDetails, order are not used any longer,
     *  but are left here for simplicity on future refactors / changes
     *
     * 'category' identifies the group and type
     * getGroup() and getType() use this array
     *
     * @JMS\Exclude
     */
    public static $categories = [
        // type (in/out) => category => group['categories'] => category => hasMoreDetails

        // Money In
        'in' => [
            'salary-or-wages' => [
                'categories' => [],
                'hasDetails' => false
            ],
            'income-and-earnings' => [
                'categories' => [
                    'account-interest' => false,
                    'dividends' => false,
                    'income-from-property-rental' => false,
                ]
            ],
            'pensions' => [
                'categories' => [
                    'personal-pension' => false,
                    'state-pension' => false,
                ]
            ],
            'state-benefits' => [
                'categories' => [
                    'attendance-allowance' => false,
                    'disability-living-allowance' => false,
                    'employment-support-allowance' => false,
                    'housing-benefit' => false,
                    'incapacity-benefit' => false,
                    'income-support' => false,
                    'pension-credit' => false,
                    'personal-independence-payment' => false,
                    'severe-disablement-allowance' => false,
                    'universal-credit' => false,
                    'winter-fuel-cold-weather-payment' => false,
                    'other-benefits' => true
                ]
            ],
            'damages' => [
                'categories' => [
                    'compensation-or-damages-award' => true
                ]
            ],
            'one-off' => [
                'categories' => [
                    'bequest-or-inheritance' => false,
                    'cash-gift-received' => false,
                    'refunds' => false,
                    'sale-of-asset' => true,
                    'sale-of-investment' => true,
                    'sale-of-property' => true,
                ]
            ],
            'moneyin-other' => [
                'categories' => [
                    'anything-else' => true
                ]
            ]
        ],
        'out' => [
            'household-bills' => [
                'categories' => [
                    'broadband' => false,
                    'council-tax' => false,
                    'electricity', false,
                    'food' => false,
                    'gas' => false,
                    'insurance-eg-life-home-contents' => false,
                    'other-insurance' => false,
                    'property-maintenance-improvement' => true,
                    'telephone' => false,
                    'tv-services'=> false,
                    'water' => false,
                    'households-bills-other' => true,
                ]
            ],
            'accommodation' => [
                'categories' => [
                    'accommodation-service-charge' => false,
                    'mortgage' => false,
                    'rent' => false,
                    'accommodation-other' => true,
                ]
            ],
            'care-and-medical' => [
                'categories' => [
                    'care-fees' => false,
                    'local-authority-charges-for-care' => false,
                    'medical-expenses' => false,
                    'medical-insurance' => false,
                ]
            ],
            'clioent-expenses' => [
                'categories' => [
                    'client-transport-bus-train-taxi-fares' => false,
                    'clothes' => false,
                    'day-trips' => false,
                    'holidays' => false,
                    'personal-allowance-pocket-money' => false,
                    'toiletries' => false,
                ]
            ],
            'fees' => [
                'categories' => [
                    'deputy-security-bond' => false,
                    'opg-fees' => false,
                    'professional-fees-eg-solicitor-accountant' => false,
                    'other-fees' => true
                ]
            ],
            'major-purchases' => [
                'categories' => [
                    'investment-bonds-purchased' => true,
                    'investment-account-purchased' => true,
                    'stocks-and-shares-purchased' => true,
                    'purchase-over-1000' => true
                ]
            ],
            'debt-and-charges' => [
                'categories' => [
                    'bank-charges' => false,
                    'credit-cards-charges' => false,
                    'loans' => false,
                    'tax-payments-to-hmrc' => false,
                    'unpaid-care-fees' => false,
                    'debt-and-charges-other' => true,
                ]
            ],
            'moving-money' => [
                'categories' => [
                    'cash-withdrawn' => true,
                    'transfers-out-to-other-accounts' => true,
                    'anything-else-paid-out' => true,
                ]
            ]
        ]
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="transaction_id_seq", allocationSize=1, initialValue=1)
     *
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="moneyTransactions")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * Category (e.g. "dividends")
     * Once the category is known, group (income and dividends) and type (in) are known as well, see self::$categories
     *
     * @var string
     *
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @ORM\Column(name="category", type="string", length=255, nullable=false)
     */
    private $category;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var string
     *
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * MoneyTransaction constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
        $report->addMoneyTransaction($this);
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;
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
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return array
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param array $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

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
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the group based on the category
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("group")
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @return string in/out
     */
    public function getGroup()
    {
        foreach (self::$categories[$this->getType()] as $group => $catArray) {

            // if this->getCategory is in list of categories, return
            if (array_key_exists($this->getCategory(), $catArray['categories']) ||
                $group == $this->getCategory()
            ) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Get the type (in/out) based on the category
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("type")
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @return string in/out
     */
    public function getType()
    {
        $type = null;

        foreach (self::$categories['in'] as $group => $catArray) {
            // if this->getCategory is in list of categories, return
            if (array_key_exists($this->getCategory(), $catArray['categories']) ||
                $group == $this->getCategory()
            ) {
                $type = 'in';
            }
        }

        foreach (self::$categories['out'] as $group => $catArray) {
            // if this->getCategory is in list of categories, return
            if (array_key_exists($this->getCategory(), $catArray['categories']) ||
                $group == $this->getCategory()
            ) {
                if (!empty($type)) {
                    throw new \RuntimeException('Duplicate category: ' . $this->getCategory() . ' in/out transactions');
                }
                $type = 'out';
            }
        }

        return $type;
    }
}
