<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Account
 *
 * @ORM\Table(name="account_transaction")
 * @ORM\Entity
 */
class AccountTransaction
{
    /**
     * @JMS\Groups({"transactions"})
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="account_transaction_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @var Account
     * 
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Account", inversedBy="transactions")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $account;
    
    /**
     * @var AccountTransactionType
     * 
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AccountTransactionType")
     * @ORM\JoinColumn(name="account_transaction_type_id", referencedColumnName="id")
     */
    private $transactionType;
    
    /**
     * @var string
     * @JMS\Groups({"transactions"})
     *
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=false)
     */
    private $amount;
    
    /**
     * @var string
     * @JMS\Groups({"transactions"})
     *
     * @ORM\Column(name="more_details", type="text")
     */
    private $moreDetails;
    
    public function __construct(Account $account, AccountTransactionType $transactionType, $amount)
    {
        $this->account = $account;
        $this->transactionType = $transactionType;
        $this->amount = $amount;
    }
    
    public function getAccount()
    {
        return $this->account;
    }

    public function getTransactionType()
    {
        return $this->transactionType;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    public function hasMoreDetails()
    {
        
    }

    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }

    public function setTransactionType(AccountTransactionType $transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
        return $this;
    }

}
