<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ReportSubmissionRepository extends EntityRepository
{
    /**
     * @param string $status        string new|archived
     * @param string $q             serach string
     * @param string $createdByRole see values in USER::ROLE_*
     * @param int    $offset
     * @param int    $limit
     * @param string $orderBy       default createdOn
     * @param string $order         default ASC
     *
     * @return array [  counts=>[new=>integer, archived=>integer],    records => [array<ReportSubmission>]    ]
     */
    public function findByFiltersWithCounts(
        $status,
        $q,
        $createdByRole,
        $offset,
        $limit,
        $orderBy = 'createdOn',
        $order = 'ASC'
    ) {
        $statusFilters = [
            'new' => 'rs.archivedBy IS NULL',
            'archived' => 'rs.archivedBy IS NOT NULL',
        ];

        // BASE QUERY BUILDER with filters (for both count and results)
        $qb = $this->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.ndr', 'ndr')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('r.client', 'c')
            ->leftJoin('rs.documents', 'd')
        ;
        // search filter
        if ($q) {
            $qb->andWhere(implode(' OR ', [
                // user
                'lower(cb.firstname) LIKE :qLike',
                'lower(cb.lastname) LIKE :qLike',
                // client names and case number (exact match)
                'lower(c.firstname) LIKE :qLike',
                'lower(c.lastname) LIKE :qLike',
                // case number
                'c.caseNumber = :q'
            ]));
            $qb->setParameter('qLike', '%' . strtolower($q) . '%');
            $qb->setParameter('q', $q);
        }
        // role filter
        if ($createdByRole) {
            $qb->andWhere('cb.roleName LIKE :roleNameLikePrefix');
            $qb->setParameter('roleNameLikePrefix', strtoupper($createdByRole) . '%');
        }

        // get results (base query + ordered + pagination + status filter)
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(User::class); //disable softdelete for createdBy, needed from admin area
        $qbSelect = clone $qb;
        $qbSelect->select('rs');
        if (isset($statusFilters[$status])) {
            $qbSelect->andWhere($statusFilters[$status]);
        }
        $qbSelect
            ->orderBy('rs.' . $orderBy, $order)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $records = $qbSelect->getQuery()->getResult(); /* @var $records ReportSubmission[] */
        $this->_em->getFilters()->enable('softdeleteable');

        // run counts on the base query for each status (new/archived)
        $counts = [];
        foreach ($statusFilters as $k=>$v) {
            $qbCount = clone $qb;
            $counts[$k] = $qbCount->select('count(rs.id)')->andWhere($v)->getQuery()->getSingleScalarResult();
        }

        return [
            'records'=>$records,
            'counts'=>$counts,
        ];
    }

    /**
     * @param \DateTime $olderThan
     * @param int       $limit
     *
     * @return ReportSubmission[]
     */
    public function findDownloadableOlderThan(\DateTime $olderThan, $limit)
    {
        $qb = $this->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.documents', 'd')
            ->where('rs.createdOn <= :olderThan')
            ->andWhere('rs.downloadable = true')
            ->setParameter(':olderThan', $olderThan);

        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult(); /* @var $records ReportSubmission[] */
    }
}
