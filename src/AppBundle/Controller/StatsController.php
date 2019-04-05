<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Model\Stats\StatsQueryResponse;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;


class StatsController extends RestController
{
    /** @var EntityManager */
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

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
            
            $paCount = $this->countNamedDeputies('ROLE_PA_NAMED', $from, $to);
            $profCount = $this->countNamedDeputies('ROLE_PROF_NAMED', $from, $to);
            $reportCount = $this->countReports($from, $to);

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

    private function countNamedDeputies($type, $from, $to)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository(User::class)->createQueryBuilder('u');

        try{
            return $qb->select('u.id')
                ->where('u.roleName = :type')
                ->andWhere('u.registrationDate BETWEEN :from AND :to')
                ->setParameters(['from' => $from, 'to' => $to, 'type' => $type])
                ->getQuery()
                ->getSingleScalarResult();
        } catch(NoResultException $e) {
            return 0;
        }
    }

    private function countReports($from, $to)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository(Report::class)->createQueryBuilder('r');

        try{
            return $qb->select('r.id')
                ->where('r.submitDate BETWEEN :from AND :to')
                ->andWhere('r.submitted = true')
                ->setParameters(['from' => $from, 'to' => $to])
                ->getQuery()
                ->getSingleScalarResult();           
        } catch(NoResultException $e) {
            return 0;
        }
    }
}
