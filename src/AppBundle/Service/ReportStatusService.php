<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

class ReportStatusService
{
    const STATE_NOT_STARTED = 'not-started';
    const STATE_INCOMPLETE = 'incomplete';
    const STATE_DONE = 'done';
    const STATE_NOT_MATCHING = 'not-matching'; //only used for balance section
    const STATE_EXPLAINED = 'explained'; //only used for balance section


    /**
     * @JMS\Exclude
     *
     * @var Report
     */
    private $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "decision-status"})
     *
     * @return array
     */
    public function getDecisionsState()
    {
        $hasDecisions = count($this->report->getDecisions()) > 0;

        if (!$hasDecisions && !$this->report->getReasonForNoDecisions() && !$this->report->getMentalCapacity()) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        $decisionsValid = $hasDecisions || $this->report->getReasonForNoDecisions();
        if ($decisionsValid && $this->report->getMentalCapacity() &&
            $this->report->getMentalCapacity()->getHasCapacityChanged()
            && $this->report->getMentalCapacity()->getMentalAssessmentDate()
        ) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getDecisions())];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "contact-status"})
     *
     * @return array
     */
    public function getContactsState()
    {
        $hasContacts = count($this->report->getContacts()) > 0;
        if (!$hasContacts && empty($this->report->getReasonForNoContacts())) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        } else {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getContacts())];
        }
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "visits-care-state"})
     *
     * @return array
     */
    public function getVisitsCareState()
    {
        $visitsCare = $this->report->getVisitsCare();
        $answers = $visitsCare ? [
            $visitsCare->getDoYouLiveWithClient(),
            $visitsCare->getDoesClientReceivePaidCare(),
            $visitsCare->getWhoIsDoingTheCaring(),
            $visitsCare->getDoesClientHaveACarePlan(),
        ] : [];

        switch (count(array_filter($answers))) {
            case 0:
                return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
            case count($answers):
                return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
            default:
                return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "account-state"})
     *
     * @return array
     */
    public function getBankAccountsState()
    {
        $bankAccounts = $this->report->getBankAccounts();
        if (count($bankAccounts) === 0) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if (count($this->report->getBankAccountsIncomplete()) > 0) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => count($bankAccounts)];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "money-transfer-state"})
     *
     * @return array
     */
    public function getMoneyTransferState()
    {
        $hasAtLeastOneTransfer = count($this->report->getMoneyTransfers()) >= 1;
        $valid = $hasAtLeastOneTransfer || $this->report->getNoTransfersToAdd();

        if ($valid || count($this->report->getBankAccounts()) <= 1) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransfers())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "money-in-state"})
     *
     * @return array
     */
    public function getMoneyInState()
    {
        if ($this->report->hasMoneyIn()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransactionsIn())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "money-out-state"})
     *
     * @return array
     */
    public function getMoneyOutState()
    {
        if ($this->report->hasMoneyOut()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransactionsOut())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "money-in-short-state"})
     *
     * @return array
     */
    public function getMoneyInShortState()
    {
        $categoriesCount = count($this->report->getMoneyShortCategoriesInPresent());
        $transactionsExist = $this->report->getMoneyTransactionsShortInExist();
        $isCompleted = ('no' == $transactionsExist || ('yes' == $transactionsExist and count($this->report->getMoneyTransactionsShortIn()) > 0));

        if ($isCompleted) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransactionsShortIn())];
        }

        if ($categoriesCount) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "money-out-short-state"})
     *
     * @return array
     */
    public function getMoneyOutShortState()
    {
        $categoriesCount = count($this->report->getMoneyShortCategoriesOutPresent());
        $transactionsExist = $this->report->getMoneyTransactionsShortOutExist();
        $isCompleted = ('no' == $transactionsExist || ('yes' == $transactionsExist and count($this->report->getMoneyTransactionsShortOut()) > 0));

        if ($isCompleted) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransactionsShortOut())];
        }

        if ($categoriesCount) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "balance-state"})
     *
     * see https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/152502291
     *
     * @return array
     */
    public function getBalanceState()
    {
        // if the section does not exist, "done" is returned. Although in that case this method shouldn't be called/needed
        if (!$this->report->hasSection(Report::SECTION_BALANCE)) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
        }

        if ($this->report->isMissingMoneyOrAccountsOrClosingBalance()
            || $this->getGiftsState()['state'] != self::STATE_DONE
            || $this->getExpensesState()['state'] != self::STATE_DONE // won't be true if the section is not in the report type
            || $this->getPaFeesExpensesState()['state'] != self::STATE_DONE // won't be true if the section is not in the report type
        ) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($this->report->getTotalsMatch()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0]; // balance matching => complete
        }

        if ($this->report->getBalanceMismatchExplanation()) {
            return ['state' => self::STATE_EXPLAINED, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_NOT_MATCHING, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("boolean")
     * @JMS\Groups({"status"})
     *
     * @return bool
     */
    public function isReadyToSubmit()
    {
        return count($this->getRemainingSections()) === 0;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "asset-state"})
     *
     * @return array
     */
    public function getAssetsState()
    {
        $hasAtLeastOneAsset = count($this->report->getAssets()) > 0;
        $noAssetsToAdd = $this->report->getNoAssetToAdd();

        if (!$hasAtLeastOneAsset && !$noAssetsToAdd) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($hasAtLeastOneAsset || $noAssetsToAdd) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getAssets())];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "debt-state"})
     *
     * @return array
     */
    public function getDebtsState()
    {
        $hasDebts = $this->report->getHasDebts();
        if (empty($hasDebts)) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        } elseif ('no' == $hasDebts ||
            (   'yes' == $hasDebts &&
                count($this->report->getDebtsWithValidAmount()) > 0) &&
                !empty($this->report->getDebtManagement())
        )
        {
            return ['state' => self::STATE_DONE];
        } else {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => count($this->report->getDebtsWithValidAmount())];
        }
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "fee-state"})
     *
     * @return array
     */
    public function getPaFeesExpensesState()
    {
        // if the section is not relevant for the report, then it's done
        if (!$this->report->hasSection(Report::SECTION_PA_DEPUTY_EXPENSES)) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
        }

        if ($this->report->paFeesExpensesNotStarted()) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($this->report->paFeesExpensesCompleted()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "action-state"})
     *
     * @return array
     */
    public function getActionsState()
    {
        $action = $this->report->getAction();
        $answers = $action ? [
            $action->getDoYouHaveConcerns(),
            $action->getDoYouExpectFinancialDecisions(),
        ] : [];

        switch (count(array_filter($answers))) {
            case 0:
                return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
            case count($answers):
                return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
            default:
                return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "more-info-state"})
     *
     * @return array
     */
    public function getOtherInfoState()
    {
        if ($this->report->getActionMoreInfo() === null) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "documents-state"})
     *
     * @return array
     */
    public function getDocumentsState()
    {
        $numRecords = count($this->report->getDocuments());

        if ($this->report->getWishToProvideDocumentation() === null || ($this->report->getWishToProvideDocumentation() === 'yes' && $numRecords == 0)) {
            $status = ['state' => self::STATE_NOT_STARTED];
        } else {
            $status = ['state' => self::STATE_DONE];
        }

        return array_merge($status, ['nOfRecords' => $numRecords]);
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "expenses-state"})
     *
     * @return array
     */
    public function getExpensesState()
    {
        // if the section is not relevant for the report, then it's "done"
        if (!$this->report->hasSection(Report::SECTION_DEPUTY_EXPENSES)) {
            return ['state' => self::STATE_DONE];
        }

        if ($this->report->expensesSectionCompleted()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getExpenses())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "gifts-state"})
     *
     * @return array
     */
    public function getGiftsState()
    {
        if ($this->report->giftsSectionCompleted()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getGifts())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status"})
     *
     * @return array
     */
    public function getRemainingSections()
    {
        return array_filter($this->getSectionStatus(), function ($e) {
            return ($e != self::STATE_DONE) && ($e != self::STATE_EXPLAINED);
        }) ?: [];
    }

    private function getSectionState($section)
    {
        switch ($section) {
            case Report::SECTION_DECISIONS:
                return $this->getDecisionsState()['state'];
            case Report::SECTION_CONTACTS:
                return $this->getContactsState()['state'];
            case Report::SECTION_VISITS_CARE:
                return $this->getVisitsCareState()['state'];
            case Report::SECTION_LIFESTYLE:
                return $this->getLifestyleState()['state'];
            // money
            case Report::SECTION_BALANCE:
                return $this->getBalanceState()['state'];
            case Report::SECTION_BANK_ACCOUNTS:
                return $this->getBankAccountsState()['state'];
            case Report::SECTION_MONEY_TRANSFERS:
                return $this->getMoneyTransferState()['state'];
            case Report::SECTION_MONEY_IN:
                return $this->getMoneyInState()['state'];
            case Report::SECTION_MONEY_OUT:
                return $this->getMoneyOutState()['state'];
            case Report::SECTION_MONEY_IN_SHORT:
                return $this->getMoneyInShortState()['state'];
            case Report::SECTION_MONEY_OUT_SHORT:
                return $this->getMoneyOutShortState()['state'];
            case Report::SECTION_ASSETS:
                return $this->getAssetsState()['state'];
            case Report::SECTION_DEBTS:
                return $this->getDebtsState()['state'];
            case Report::SECTION_GIFTS:
                return $this->getGiftsState()['state'];
            // end money
            case Report::SECTION_ACTIONS:
                return $this->getActionsState()['state'];
            case Report::SECTION_OTHER_INFO:
                return $this->getOtherInfoState()['state'];
            case Report::SECTION_DEPUTY_EXPENSES:
                return $this->getExpensesState()['state'];
            case Report::SECTION_PA_DEPUTY_EXPENSES:
                return $this->getPaFeesExpensesState()['state'];
            case Report::SECTION_DOCUMENTS:
                return $this->getDocumentsState()['state'];
            default:
                throw new \InvalidArgumentException(__METHOD__ . " $section section not defined");
        }
    }

    /**
     * Get section for the specific report type, along with the status
     *
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status"})
     *
     * @return array of section=>state
     */
    public function getSectionStatus()
    {
        $ret = [];
        foreach ($this->report->getAvailableSections() as $sectionId) {
            $ret[$sectionId] = $this->getSectionState($sectionId);
        }

        return $ret;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "lifestyle-state"})
     *
     * @return array
     */
    public function getLifestyleState()
    {
        $lifestyle = $this->report->getLifestyle();
        $answers = $lifestyle ? [
            $lifestyle->getCareAppointments(),
            $lifestyle->getDoesClientUndertakeSocialActivities(),
        ] : [];

        switch (count(array_filter($answers))) {
            case 0:
                return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
            case 2:
                return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
            default:
                return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status"})
     *
     * @return array
     */
    public function getSubmitState()
    {
        return [
            'state'      => $this->isReadyToSubmit() && $this->report->isDue() ? self::STATE_DONE : self::STATE_NOT_STARTED,
            'nOfRecords' => 0,
        ];
    }

    /**
     * @return bool
     */
    public function hasStarted()
    {
        $sectionStatus = $this->getSectionStatus();
        // exclude balance, and money transfers, that depend on other section therefore not required
        // to complete and therefore considered "done"
        unset($sectionStatus['balance']);
        unset($sectionStatus['moneyTransfers']);

        return count(array_filter($sectionStatus, function ($e) {
            return $e != self::STATE_NOT_STARTED;
        })) > 0;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("string")
     * @JMS\Groups({"status"})
     *
     * @return string
     */
    public function getStatus()
    {
        if (!$this->hasStarted()) {
            return 'notStarted';
        }

        if ($this->report->isDue() && $this->isReadyToSubmit()) {
            return 'readyToSubmit';
        } else {
            return 'notFinished';
        }
    }
}
