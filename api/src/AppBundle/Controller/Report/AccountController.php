<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report as Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends RestController
{
    private $sectionIds = [Report::SECTION_BANK_ACCOUNTS];

    /**
     * @Route("/report/{reportId}/account")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAccountAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'opening_balance' => 'mustExist',
        ]);

        $account = new EntityDir\Report\BankAccount();
        $account->setReport($report);

        $this->fillAccountData($account, $data);

        $this->persistAndFlush($account);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $account->getId()];
    }

    /**
     * @Route("/report/account/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found');
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['account'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $account;
    }

    /**
     * @Route("/account/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function editAccountAction(Request $request, $id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount*/
        $report = $account->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $data = $this->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $account->setLastEdit(new \DateTime());
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        $this->setJmsSerialiserGroups(['account']);

        return $account;
    }

    /**
     * @Route("/account/{id}/dependent-records")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function accountDependentRecords($id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount */
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $report = $account->getReport();

        $transferFilter = function ($transfer) use ($account) {
            return $transfer->getFrom() === $account || $transfer->getTo() === $account;
        };

        $paymentsFilter = function ($expense) use ($account) {
            return $expense->getBankAccount() === $account;
        };

        $ret = [
            Report::SECTION_MONEY_TRANSFERS => $report->hasSection(Report::SECTION_MONEY_TRANSFERS)
                ? count($report->getMoneyTransfers()->filter($transferFilter))
                : null,
            'transactions' => [
                Report::SECTION_DEPUTY_EXPENSES => $report->hasSection(Report::SECTION_DEPUTY_EXPENSES)
                    ?  count($report->getExpenses()->filter($paymentsFilter))
                    : null,
                Report::SECTION_GIFTS => $report->hasSection(Report::SECTION_GIFTS)
                    ? count($report->getGifts()->filter($paymentsFilter))
                    : null,
                Report::SECTION_MONEY_IN => $report->hasSection(Report::SECTION_MONEY_IN)
                   ? count($report->getMoneyTransactionsIn()->filter($paymentsFilter))
                    : null,
                Report::SECTION_MONEY_OUT => $report->hasSection(Report::SECTION_MONEY_OUT)
                    ? count($report->getMoneyTransactionsOut()->filter($paymentsFilter))
                    : null
            ]
        ];

        $ret['transactionsCount'] = array_sum($ret['transactions']);

        return $ret;
    }

    /**
     * @Route("/account/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function accountDelete($id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount */
        $report = $account->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->getEntityManager()->remove($account);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }

    private function fillAccountData(EntityDir\Report\BankAccount $account, array $data)
    {
        //basicdata
        if (array_key_exists('account_type', $data)) {
            $account->setAccountType($data['account_type']);
        }

        if ($account->requiresBankName()) {
            if (array_key_exists('bank', $data)) {
                $account->setBank($data['bank']);
            }
        } else {
            $account->setBank(null);
        }

        if ($account->requiresSortCode()) {
            if (array_key_exists('sort_code', $data)) {
                $account->setSortCode($data['sort_code']);
            }
        } else {
            $account->setSortCode(null);
        }

        if (array_key_exists('account_number', $data)) {
            $account->setAccountNumber($data['account_number']);
        }

        if (array_key_exists('opening_balance', $data)) {
            $account->setOpeningBalance($data['opening_balance']);
        }

        if (array_key_exists('is_closed', $data)) {
            $account->setIsClosed((boolean) $data['is_closed']);
        }

        if (array_key_exists('closing_balance', $data)) {
            $account->setClosingBalance($data['closing_balance']);
        }

        if (array_key_exists('is_joint_account', $data)) {
            $account->setIsJointAccount($data['is_joint_account']);
        }
    }
}
