<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\Debt as ReportDebt;
use AppBundle\Entity\Report\Fee as ReportFee;
use AppBundle\Entity\Report\MoneyShortCategory as ReportMoneyShortCategory;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class ReportSubmissionRepository extends EntityRepository
{
    /**
     * deleted user are still returned
     * @param boolean $archived
     * @return ArrayCollection|ReportSubmission[]
     */
    public function getReportSubmissions($archived)
    {
        $qb = $this->_em->getRepository(ReportSubmission::class)
            ->createQueryBuilder('rs')
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('r.client', 'c')
            ->leftJoin('rs.documents', 'd')
            ->orderBy('rs.id', 'DESC');

        if ($archived) {
            $qb->andWhere('rs.archivedBy is not null' );
        } else {
            $qb->andWhere('rs.archivedBy is null' );
        }

        $this->_em->getFilters()->disable('softdeleteable');
        $results = $qb->getQuery()->getResult(); /* @var $results ReportSubmission[] */
        $this->_em->getFilters()->enable('softdeleteable');

        return $results;
    }
}
