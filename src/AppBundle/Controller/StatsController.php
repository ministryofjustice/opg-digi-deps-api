<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;


class SettingController extends RestController
{
    /**
     * @Route("/stats")
     * @Method({"GET"})
     */
    public function getStats(Request $request)
    {
        // Get dates for request
        $dates = $request->getContent();

        /** @var $em \Doctrine\ORM\EntityManager */ 
        $em = $this->getContainer()->get('em'); 
        $from = $dates['from'];
        $to = $dates['to'];

        $paCount = $this->countNamedDeputies('ROLE_PA_NAMED', $from, $to);
        $profCount = $this->countNamedDeputies('ROLE_PROF_NAMED', $from, $to);
        $reportCount = $this->countReports();

        $queryResponse = new StatsQueryResponse();
        $queryResponse->setPaNamedDeputyCount($paCount);
        $queryResponse->setProfNamedDeputyCount($profCount);
        $queryResponse->setReportCount($reportCount);

        return new JsonResponse($queryResponse->toArray());
    }

    private function countNamedDeputies($type, $from, $to)
    {
        $qb = $em->getRepository(User::class)
        ->createQueryBuilder('u');

        $query = $qb->select('u')
        ->where('u.roleName IS :type')
        ->andWhere('u.registrationDate BETWEEN :from AND :to')
        ->setParameters(['from' => $from, 'to' => $to, 'type' => $type]);

        return $qb->getQuery()->getSingleScalarResult();
    }

    private function countReports()
    {
        $qb = $em->getRepository(Report::class)
        ->createQueryBuilder('r');

        $query = $qb->select('r')
        ->where('r.registrationDate BETWEEN :from AND :to')
        ->andWhere('r.submitted IS true')
        ->setParameters(['from' => $from, 'to' => $to]);

        return $qb->getQuery()->getSingleScalarResult();   
    }
}