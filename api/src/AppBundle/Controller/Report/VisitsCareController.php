<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report")
 */
class VisitsCareController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_VISITS_CARE];

    /**
     * @Route("/visits-care")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAction(Request $request)
    {
        $visitsCare = new EntityDir\Report\VisitsCare();
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $data['report_id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $visitsCare->setReport($report);
        $this->updateInfo($data, $visitsCare);

        $this->getEntityManager()->persist($visitsCare);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/visits-care/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function updateAction(Request $request, $id)
    {
        $visitsCare = $this->findEntityBy(EntityDir\Report\VisitsCare::class, $id);
        $report = $visitsCare->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        $data = $this->deserializeBodyContent($request);
        $this->updateInfo($data, $visitsCare);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/{reportId}/visits-care")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $reportId
     */
    public function findByReportIdAction($reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $ret = $this->getRepository(EntityDir\Report\VisitsCare::class)->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/visits-care/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $serialiseGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['visits-care'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $visitsCare = $this->findEntityBy(EntityDir\Report\VisitsCare::class, $id, 'VisitsCare with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        return $visitsCare;
    }

    /**
     * @Route("/visits-care/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteVisitsCare($id)
    {
        $visitsCare = $this->findEntityBy(EntityDir\Report\VisitsCare::class, $id, 'VisitsCare not found');
        $report = $visitsCare->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        $this->getEntityManager()->remove($visitsCare);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @param array                       $data
     * @param EntityDir\Report\VisitsCare $visitsCare
     *
     * @return \AppBundle\Entity\Report\Report $report
     */
    private function updateInfo(array $data, EntityDir\Report\VisitsCare $visitsCare)
    {
        if (array_key_exists('do_you_live_with_client', $data)) {
            $visitsCare->setDoYouLiveWithClient($data['do_you_live_with_client']);
        }

        if (array_key_exists('does_client_receive_paid_care', $data)) {
            $visitsCare->setDoesClientReceivePaidCare($data['does_client_receive_paid_care']);
        }

        if (array_key_exists('how_often_do_you_contact_client', $data)) {
            $visitsCare->setHowOftenDoYouContactClient($data['how_often_do_you_contact_client']);
        }

        if (array_key_exists('how_is_care_funded', $data)) {
            $visitsCare->setHowIsCareFunded($data['how_is_care_funded']);
        }

        if (array_key_exists('who_is_doing_the_caring', $data)) {
            $visitsCare->setWhoIsDoingTheCaring($data['who_is_doing_the_caring']);
        }

        if (array_key_exists('does_client_have_a_care_plan', $data)) {
            $visitsCare->setDoesClientHaveACarePlan($data['does_client_have_a_care_plan']);
        }

        if (array_key_exists('when_was_care_plan_last_reviewed', $data)) {
            if (!empty($data['when_was_care_plan_last_reviewed'])) {
                $visitsCare->setWhenWasCarePlanLastReviewed(new \DateTime($data['when_was_care_plan_last_reviewed']));
            } else {
                $visitsCare->setWhenWasCarePlanLastReviewed(null);
            }
        }

        return $visitsCare;
    }
}
