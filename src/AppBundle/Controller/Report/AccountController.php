<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends RestController
{
    /**
     * @Route("/report/{reportId}/account")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAccountAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'opening_balance' => 'mustExist',
        ]);

        $account = new EntityDir\Report\BankAccount();
        $account->setReport($report);

        $this->fillAccountData($account, $data);

        $this->persistAndFlush($account);

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
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $data = $this->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $account->setLastEdit(new \DateTime());

        $this->getEntityManager()->flush();

        $this->setJmsSerialiserGroups(['account']);

        return $account;
    }

    /**
     * @Route("/account/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function accountDelete($id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount */
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $this->denyAccessIfAccountHasTransfers($account);

        $this->getEntityManager()->remove($account);

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

    /**
     * Check bank account has transfers
     *
     * @param EntityDir\Report\BankAccount $account
     */
    protected function denyAccessIfAccountHasTransfers(EntityDir\Report\BankAccount $account)
    {
        $transfers = $account->getReport()->getMoneyTransfers();

        /** @var EntityDir\Report\MoneyTransfer $transfer */
        foreach ($transfers as $transfer) {
            if ($account === $transfer->getFrom() || ($account === $transfer->getTo())) {
                throw new \RuntimeException(
                    'report.bankAccount.deleteWithTransfers',
                    401
                );
            }
        }
    }
}
