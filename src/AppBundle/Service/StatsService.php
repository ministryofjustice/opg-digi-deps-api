<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class StatsService
{
    /** @var EntityManager */
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function countNamedDeputies($type, $from, $to)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository(User::class)->createQueryBuilder('u');

        try{
            return $qb->select('count(u.id)')
                ->where('u.roleName = :type')
                ->andWhere('u.registrationDate BETWEEN :from AND :to')
                ->setParameters(['from' => $from, 'to' => $to, 'type' => $type])
                ->getQuery()
                ->getSingleScalarResult();
        } catch(NoResultException $e) {
            return 0;
        }
    }

    public function countReports($from, $to)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository(Report::class)->createQueryBuilder('r');

        try{
            return $qb->select('count(r.id)')
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
