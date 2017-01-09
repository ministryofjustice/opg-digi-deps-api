<?php

namespace AppBundle\Controller\Odr;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Controller\RestController;

class OdrController extends RestController
{
    /**
     * @Route("/odr/{id}")
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function getById(Request $request, $id)
    {
        $groups = $request->query->has('groups') ? (array)$request->query->get('groups') : ['odr'];
        $this->setJmsSerialiserGroups($groups);

        /* @var $report EntityDir\Odr\Odr */
        $report = $this->findEntityBy('Odr\Odr', $id);

        if (!$this->isGranted(EntityDir\Role::ADMIN)) {
            $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
            $this->denyAccessIfOdrDoesNotBelongToUser($report);
        }

        return $report;
    }

    /**
     * @Route("/odr/{id}/submit")
     * @Method({"PUT"})
     */
    public function submit(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy('Odr\Odr', $id, 'Odr not found');
        /* @var $odr EntityDir\Odr\Odr */
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $data = $this->deserializeBodyContent($request);

        if (empty($data['agreed_behalf_deputy'])) {
            throw new \InvalidArgumentException('Missing agreed_behalf_deputy');
        }
        $odr->setAgreedBehalfDeputy($data['agreed_behalf_deputy']);
        if ($data['agreed_behalf_deputy'] === 'more_deputies_not_behalf') {
            $odr->setAgreedBehalfDeputyExplanation($data['agreed_behalf_deputy_explanation']);
        } else {
            $odr->setAgreedBehalfDeputyExplanation(null);
        }

        $odr->setSubmitted(true);
        $odr->setSubmitDate(new \DateTime($data['submit_date']));

        $this->getEntityManager()->flush($odr);

        //response to pass back
        return [];
    }


    /**
     * @Route("/odr/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        /* @var $odr EntityDir\Odr\Odr */
        $odr = $this->findEntityBy('Odr\Odr', $id, 'Odr not found');

        if (!$this->isGranted(EntityDir\Role::ADMIN)) {
            $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
            $this->denyAccessIfOdrDoesNotBelongToUser($odr);
        }

        $data = $this->deserializeBodyContent($request);

        if (array_key_exists('has_debts', $data) && in_array($data['has_debts'], ['yes', 'no'])) {
            $odr->setHasDebts($data['has_debts']);
            // null debts
            foreach ($odr->getDebts() as $debt) {
                $debt->setAmount(null);
                $debt->setMoreDetails(null);
                $this->getEntityManager()->flush($debt);
            }
            // set debts as per "debts" key
            foreach ($data['debts'] as $row) {
                $debt = $odr->getDebtByTypeId($row['debt_type_id']);
                if (!$debt instanceof EntityDir\Odr\Debt) {
                    continue; //not clear when that might happen. kept similar to transaction below
                }
                $debt->setAmount($row['amount']);
                $debt->setMoreDetails($debt->getHasMoreDetails() ? $row['more_details'] : null);
                $this->getEntityManager()->flush($debt);
                $this->setJmsSerialiserGroups(['debts']); //returns saved data (AJAX operations)
            }
        }

        if (array_key_exists('state_benefits', $data)) {
            foreach ($data['state_benefits'] as $row) {
                $e = $odr->getStateBenefitByTypeId($row['type_id']);
                if ($e instanceof EntityDir\Odr\IncomeBenefitStateBenefit) {
                    $e
                        ->setPresent($row['present'])
                        ->setMoreDetails($row['present'] ? $row['more_details']: null);
                    $this->getEntityManager()->flush($e);
                }
            }
        }

        if (array_key_exists('receive_state_pension', $data)) {
            $odr->setReceiveStatePension($data['receive_state_pension']);
        }

        if (array_key_exists('receive_other_income_details', $data)) {
            $odr->setReceiveOtherIncomeDetails($data['receive_other_income_details']);
        }

        if (array_key_exists('receive_other_income', $data)) {
            $odr->setReceiveOtherIncome($data['receive_other_income']);
            if ($odr->getReceiveOtherIncome() == 'no') {
                $odr->setReceiveOtherIncomeDetails(null);
            }
        }

        if (array_key_exists('expect_compensation_damages_details', $data)) {
            $odr->setExpectCompensationDamagesDetails($data['expect_compensation_damages_details']);
        }

        if (array_key_exists('expect_compensation_damages', $data)) {
            $odr->setExpectCompensationDamages($data['expect_compensation_damages']);
            if ($odr->getExpectCompensationDamages() == 'no') {
                $odr->setExpectCompensationDamagesDetails(null);
            }
        }

        if (array_key_exists('one_off', $data)) {
            foreach ($data['one_off'] as $row) {
                $e = $odr->getOneOffByTypeId($row['type_id']);
                if ($e instanceof EntityDir\Odr\IncomeBenefitOneOff) {
                    $e->setPresent($row['present'])->setMoreDetails($row['more_details']);
                    $this->getEntityManager()->flush($e);
                }
            }
        }

        if (array_key_exists('no_asset_to_add', $data)) {
            $odr->setNoAssetToAdd($data['no_asset_to_add']);
            if ($odr->getNoAssetToAdd()) {
                foreach ($odr->getAssets() as $asset) {
                    $this->getEntityManager()->remove($asset);
                }
                $this->getEntityManager()->flush();
            }
        }

        if (array_key_exists('paid_for_anything', $data)) {
            $odr->setPaidForAnything($data['paid_for_anything']);
            if ($odr->getPaidForAnything() === 'no') { // remove existing expenses
                foreach ($odr->getExpenses() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
        }

        // actions
        if (array_key_exists('action_give_gifts_to_client', $data)) {
            $odr->setActionGiveGiftsToClient($data['action_give_gifts_to_client']);
            if (array_key_exists('action_give_gifts_to_client_details', $data)) {
                $odr->setActionGiveGiftsToClientDetails(
                    $data['action_give_gifts_to_client'] == 'yes' ? $data['action_give_gifts_to_client_details'] : null
                );
            }
        }

        if (array_key_exists('action_property_maintenance', $data)) {
            $odr->setActionPropertyMaintenance($data['action_property_maintenance']);
        }

        if (array_key_exists('action_property_selling_rent', $data)) {
            $odr->setActionPropertySellingRent($data['action_property_selling_rent']);
        }

        if (array_key_exists('action_property_buy', $data)) {
            $odr->setActionPropertyBuy($data['action_property_buy']);
        }

        if (array_key_exists('action_more_info', $data)) {
            $odr->setActionMoreInfo($data['action_more_info']);
            if (array_key_exists('action_more_info_details', $data)) {
                $odr->setActionMoreInfoDetails(
                    $data['action_more_info'] == 'yes' ? $data['action_more_info_details'] : null
                );
            }
        }

        if (array_key_exists('start_date', $data)) {
            $odr->setStartDate(new \DateTime($data['start_date']));
        }

        $this->getEntityManager()->flush();

        return ['id' => $odr->getId()];
    }

    /**
     * REMOVE THIS WHEN OTPP IS MERGED
     * @Route("/odr/{id}/reset-data-dev")
     * @Method({"PUT"})
     */
    public function resetDataDev(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy('Odr\Odr', $id, 'Odr not found');
        /* @var $odr EntityDir\Odr\Odr */

        $em = $this->getEntityManager();

        if ($odr->getVisitsCare()) {
            $em->remove($odr->getVisitsCare());
        }

        foreach ($odr->getExpenses() as $e){
            $em->remove($e);
        }
        $odr->setPaidForAnything(null);

        foreach($odr->getStateBenefits() as $e) {
            $e->setPresent(false);
            $e->setMoreDetails(null);
        }
        $odr->setReceiveStatePension(null);
        $odr->setReceiveOtherIncome(null);
        $odr->setReceiveOtherIncomeDetails(null);
        $odr->setExpectCompensationDamages(null);
        $odr->setExpectCompensationDamagesDetails(null);
        foreach($odr->getOneOff() as $e) {
            $e->setPresent(false);
            $e->setMoreDetails(null);
        }

        foreach ($odr->getBankAccounts() as $e){
            $em->remove($e);
        }

        foreach ($odr->getAssets() as $e){
            $em->remove($e);
        }
        $odr->setNoAssetToAdd(null);

        foreach ($odr->getDebts() as $e){
            $e->setAmount(null);
        }
        $odr->setHasDebts(null);

        $odr->setActionGiveGiftsToClient(null);
        $odr->setActionGiveGiftsToClientDetails(null);
        $odr->setActionPropertyBuy(null);
        $odr->setActionPropertyMaintenance(null);
        $odr->setActionPropertySellingRent(null);

        $odr->setActionMoreInfo(null);
        $odr->setActionMoreInfoDetails(null);

        $em->flush();
    }
}
