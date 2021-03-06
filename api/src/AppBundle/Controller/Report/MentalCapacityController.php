<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class MentalCapacityController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_DECISIONS];

    /**
     * @Route("/report/{reportId}/mental-capacity")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function updateAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $mc = $report->getMentalCapacity();
        if (!$mc) {
            $mc = new EntityDir\Report\MentalCapacity($report);
            $this->getEntityManager()->persist($mc);
        }

        $data = $this->deserializeBodyContent($request);
        $this->updateEntity($data, $mc);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();


        return ['id' => $mc->getId()];
    }

    /**
     * @Route("/report/{reportId}/mental-capacity")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $mc = $this->findEntityBy(EntityDir\Report\MentalCapacity::class, $id, 'MentalCapacity with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($mc->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['mental-capacity'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $mc;
    }

    /**
     * @param array                           $data
     * @param EntityDir\Report\MentalCapacity $mc
     *
     * @return \AppBundle\Entity\Report\Report $report
     */
    private function updateEntity(array $data, EntityDir\Report\MentalCapacity $mc)
    {
        if (array_key_exists('has_capacity_changed', $data)) {
            $mc->setHasCapacityChanged($data['has_capacity_changed']);
        }

        if (array_key_exists('has_capacity_changed_details', $data)) {
            $mc->setHasCapacityChangedDetails($data['has_capacity_changed_details']);
        }

        if (array_key_exists('mental_assessment_date', $data)) {
            $mc->setMentalAssessmentDate(new \DateTime($data['mental_assessment_date']));
        }

        $mc->cleanUpUnusedData();

        return $mc;
    }
}
