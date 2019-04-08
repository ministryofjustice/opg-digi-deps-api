<?php

namespace AppBundle\Controller;

use AppBundle\Model\Stats\StatsQueryResponse;
use AppBundle\Service\StatsService;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;


class StatsController extends RestController
{
    /**
     * @Route("/stats")
     * @Method({"GET"})
     * @param Request $request
     */
    public function getStats(Request $request)
    {
        try {
            $params = $request->query->all();

            $from = isset($params['from']) ? new DateTime($params['from']) : new Datetime('-7 days');
            $to = isset($params['to']) ? new DateTime($params['to']) : new Datetime('now');

            $statsService = $this->container->get('opg_digideps.stats_service');
            
            $paCount = $statsService->countNamedDeputies('ROLE_PA_NAMED', $from, $to);
            $profCount = $statsService->countNamedDeputies('ROLE_PROF_NAMED', $from, $to);
            $reportCount = $statsService->countReports($from, $to);

            $queryResponse = new StatsQueryResponse();
            $queryResponse->setPaNamedDeputyCount($paCount);
            $queryResponse->setProfNamedDeputyCount($profCount);
            $queryResponse->setReportsCount($reportCount);
            $queryResponse->setFrom($from);
            $queryResponse->setTo($to);

            return $queryResponse;        
        } catch(Throwable $e) {
            return new Response($e->getMessage());
        }
        
    }

    
}
