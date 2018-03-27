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
        // group => categories[] => category => config['hasMoreDetails', 'type']
        // Money In
        'salary-or-wages' => [
            'categories' => [],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'income-and-earnings' => [
            'categories' => [
                'account-interest' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'dividends' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'income-from-property-rental' => ['config' => ['hasDetails' => false, 'type' => 'in']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'pensions' => [
            'categories' => [
                'personal-pension' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'state-pension' => ['config' => ['hasDetails' => false, 'type' => 'in']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'state-benefits' => [
            'categories' => [
                'attendance-allowance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'disability-living-allowance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'employment-support-allowance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'housing-benefit' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'incapacity-benefit' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'income-support' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'pension-credit' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'personal-independence-payment' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'severe-disablement-allowance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'universal-credit' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'winter-fuel-cold-weather-payment' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'other-benefits' => ['config' => ['hasDetails' => true, 'type' => 'in']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'compensation-or-damages-award' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'in']
        ],
        'one-off' => [
            'categories' => [
                'bequest-or-inheritance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'cash-gift-received' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'refunds' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'sale-of-asset' => ['config' => ['hasDetails' => true, 'type' => 'in']],
                'sale-of-investment' => ['config' => ['hasDetails' => true, 'type' => 'in']],
                'sale-of-property' => ['config' => ['hasDetails' => true, 'type' => 'in']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'moneyin-other' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'in']
        ],

        // Money Out
        'household-bills' => [
            'categories' => [
                'broadband' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'council-tax' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'electricity' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'food' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'gas' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'insurance-eg-life-home-contents' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'other-insurance' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'property-maintenance-improvement' => ['config' => ['hasDetails' => true, 'type' => 'out']],
                'telephone' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'tv-services'=> ['config' => ['hasDetails' => false, 'type' => 'out']],
                'water' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'households-bills-other' => ['config' => ['hasDetails' => true, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'accommodation' => [
            'categories' => [
                'accommodation-service-charge' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'mortgage' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'rent' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'accommodation-other' => ['config' => ['hasDetails' => true, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'care-and-medical' => [
            'categories' => [
                'care-fees' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'local-authority-charges-for-care' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'medical-expenses' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'medical-insurance' => ['config' => ['hasDetails' => false, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'client-expenses' => [
            'categories' => [
                'client-transport-bus-train-taxi-fares' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'clothes' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'day-trips' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'holidays' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'personal-allowance-pocket-money' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'toiletries' => ['config' => ['hasDetails' => false, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'fees' => [
            'categories' => [
                'deputy-security-bond' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'opg-fees' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'professional-fees-eg-solicitor-accountant' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'other-fees' => ['config' => ['hasDetails' => true, 'type' => 'out']]
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'major-purchases' => [
            'categories' => [
                'investment-bonds-purchased' => ['config' => ['hasDetails' => true, 'type' => 'out']],
                'investment-account-purchased' => ['config' => ['hasDetails' => true, 'type' => 'out']],
                'stocks-and-shares-purchased' => ['config' => ['hasDetails' => true, 'type' => 'out']],
                'purchase-over-1000' => ['config' => ['hasDetails' => true, 'type' => 'out']]
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'debt-and-charges' => [
            'categories' => [
                'bank-charges' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'credit-cards-charges' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'loans' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'tax-payments-to-hmrc' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'unpaid-care-fees' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'debt-and-charges-other' => ['config' => ['hasDetails' => true, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'cash-withdrawn' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'out']
        ],
        'transfers-out-to-other-accounts' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'out']
        ],
        'moneyout-other' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'out']
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
        if ($this->isValidCategory($category)) {
            $this->category = $category;
        }
        
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
        foreach (self::$categories as $group => $catArray) {

            // if this->getCategory is in list of categories, return
            if ((isset($catArray['categories']) && array_key_exists($this->getCategory(), $catArray['categories'])) ||
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

        foreach (self::$categories as $group => $catArray) {
            if ($group == $this->getCategory()) {
                return $catArray['config']['type'];
            } elseif (array_key_exists($this->getCategory(), $catArray['categories'])) {
                $type = $catArray['categories'][$this->getCategory()]['config']['type'];
            }
        }

        return $type;
    }

    /**
     * Validates that the category chosen exists to prevent users manipulating url and storing categories we dont support
     *
     * @param $category
     * @return bool
     */
    private function isValidCategory($category)
    {
        foreach (self::$categories as $group => $catArray) {
            if ($group === strtolower($category) || array_key_exists($category, $catArray['categories'])) {
                return true;
            }
        }

        throw new \RuntimeException('Invalid money transaction category: ' . $category);
    }
}
