<?php

namespace AppBundle\Entity\Report;

//use AppBundle\Entity\Report\Transaction;
//use AppBundle\Entity\Report\Account;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    private $report;

    public function setUp()
    {
        $this->report = new Report();
    }

    public function testGetMoneyInTotal()
    {
        $this->assertEquals(0, $this->report->getMoneyInTotal());

        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(1));
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(1));
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('dividends')->setAmount(1));

        $this->assertEquals(3, $this->report->getMoneyInTotal());
    }

    public function getMoneyOutTotalProvider($expected, array $data)
    {
        $this->assertEquals(0, $this->report->getMoneyOutTotal());

        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('mortgage')->setAmount(1));
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('mortgage')->setAmount(1));
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(1));

        $this->assertEquals(3, $this->report->getMoneyOutTotal());
    }

    public function testGetAccountsOpeningBalanceTotal()
    {
        $this->assertEquals(0, $this->report->getAccountsOpeningBalanceTotal());

        $this->report->addAccount((new Account())->setBank('bank1')->setOpeningBalance(1));
        $this->report->addAccount((new Account())->setBank('bank2')->setOpeningBalance(3));
        $this->report->addAccount((new Account())->setBank('bank3')->setOpeningBalance(0));

        $this->assertEquals(4, $this->report->getAccountsOpeningBalanceTotal());
    }

    public function testGetAccountsClosingBalanceTotal()
    {
        $this->assertEquals(0, $this->report->getAccountsClosingBalanceTotal());

        $this->report->addAccount((new Account())->setBank('bank1')->setClosingBalance(1));

        $this->assertEquals(1, $this->report->getAccountsClosingBalanceTotal());

        $this->report->addAccount((new Account())->setBank('bank2')->setClosingBalance(3));
        $this->report->addAccount((new Account())->setBank('bank3')->setClosingBalance(0));

        $this->assertEquals(4, $this->report->getAccountsClosingBalanceTotal());
    }

    public function testGetCalculatedBalance()
    {
        $this->assertEquals(0, $this->report->getCalculatedBalance());

        $this->report->addAccount((new Account())->setBank('bank1')->setOpeningBalance(1));

        $this->assertEquals(1, $this->report->getCalculatedBalance());

        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(20)); //in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(20));//in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(15));//out
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(15));//out
        $calculatedBalance = 1 + 20 + 20 - 15 - 15;

        $this->assertEquals($calculatedBalance, $this->report->getCalculatedBalance());
    }

    public function testGetTotalsOffsetAndMatch()
    {
        $this->assertEquals(0, $this->report->getTotalsOffset());
        $this->assertEquals(true, $this->report->getTotalsMatch());

        // account opened with 1000, closed with 2000. 1500 money in, 400 out. balance is 100
        $this->report->addAccount((new Account())->setBank('bank1')->setOpeningBalance(1000)->setClosingBalance(2000));
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(1500));//in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(400));//out

        $this->assertEquals(100, $this->report->getTotalsOffset());
        $this->assertEquals(false, $this->report->getTotalsMatch());

        // add missing transaction that fix the balance
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(100));//out

        $this->assertEquals(0, 1000 + 1500 - 400 - 100 - 2000);
        $this->assertEquals(0, $this->report->getTotalsOffset());
        $this->assertEquals(true, $this->report->getTotalsMatch());
    }
}
