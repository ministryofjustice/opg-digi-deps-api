<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\Debt as ReportDebt;
use AppBundle\Entity\Report\Fee as ReportFee;
use AppBundle\Entity\Report\MoneyShortCategory as ReportMoneyShortCategory;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * ReportRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReportRepository extends EntityRepository
{
    /**
     * add empty Debts to Report.
     * Called from doctrine listener.
     *
     * @param Report $report
     *
     * @return int changed records
     */
    public function addDebtsToReportIfMissing(Report $report)
    {
        $ret = 0;

        // skips if already added
        if (count($report->getDebts()) > 0) {
            return $ret;
        }

        foreach (ReportDebt::$debtTypeIds as $row) {
            $debt = new ReportDebt($report, $row[0], $row[1], null);
            $this->_em->persist($debt);
            ++$ret;
        }

        return $ret;
    }

    /**
     * add empty Fees to Report.
     * Called from doctrine listener.
     *
     * @param Report $report
     *
     * @return int changed records
     */
    public function addFeesToReportIfMissing(Report $report)
    {
        // do not add if there are no PAs associated to this client
        $isPaF = function ($user) {
            return $user->isPaDeputy();
        };
        if (0 === $report->getClient()->getUsers()->filter($isPaF)->count()) {
            return;
        }

        $ret = 0;

        // skips if already added
        if (count($report->getFees()) > 0) {
            return $ret;
        }

        foreach (ReportFee::$feeTypeIds as $id => $row) {
            $debt = new ReportFee($report, $id, null);
            $this->_em->persist($debt);
            ++$ret;
        }

        return $ret;
    }

    /**
     * Called from doctrine listener.
     *
     * @param Report $report
     *
     * @return int changed records
     */
    public function addMoneyShortCategoriesIfMissing(Report $report)
    {
        $ret = 0;

        if (count($report->getMoneyShortCategories()) > 0) {
            return $ret;
        }

        $cats = ReportMoneyShortCategory::getCategories('in') + ReportMoneyShortCategory::getCategories('out');
        foreach ($cats as $typeId => $options) {
            $debt = new ReportMoneyShortCategory($report, $typeId, false);
            $this->_em->persist($debt);
            ++$ret;
        }

        return $ret;
    }


    /**
     * @param string $select reports|count
     * @param string $status see Report::STATUS_* constants
     * @param integer $userId
     * @param boolean $exclude_submitted
     * @param string $q search query client firstname/lastname or case number
     *
     * @return QueryBuilder
     */
    public function getAllReportsQb($select, $status, $userId, $exclude_submitted, $q)
    {
        $qb = $this->createQueryBuilder('r');

        if ($select == 'reports') {
            $qb
                ->select('r,c,u')
                ->leftJoin('r.submittedBy', 'sb');
        } elseif ($select == 'count') {
            $qb->select('COUNT(r)');
        } else {
            throw new \InvalidArgumentException(__METHOD__ . ": first must be reports|count");
        }

        $qb
            ->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            ->where('u.id = ' . $userId)
            ->andWhere('c.archivedAt IS NULL')
        ;

        if ($exclude_submitted) {
            $qb->andWhere('r.submitted = false OR r.submitted is null');
        }

        if ($q) {
            $qb->andWhere('lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike OR c.caseNumber = :q');
            $qb->setParameter('qLike', '%' . strtolower($q) . '%');
            $qb->setParameter('q', $q);
        }

        // note: reportStatusCached is stored ignoring due date
        $endOfToday = new \DateTime('today midnight');
        if ($status == Report::STATUS_READY_TO_SUBMIT) {
            // reports ready to submit are when reportStatusCached=readyToSubmit AND is also due (enddate < today)
            $qb->andWhere('r.reportStatusCached = :status AND r.endDate < :endOfToday')
                ->setParameter('status', $status)
                ->setParameter('endOfToday', $endOfToday);
        } else if ($status == Report::STATUS_NOT_FINISHED) {
            // report not finished are report with reportStatusCached=notFinished
            // OR ready to submit but not yet due
            $qb->andWhere('r.reportStatusCached = :status OR (r.reportStatusCached = :readyToSubmit AND r.endDate >= :endOfToday)')
                ->setParameter('status', $status)
                ->setParameter('readyToSubmit', Report::STATUS_READY_TO_SUBMIT)
                ->setParameter('endOfToday', $endOfToday);
        } else if ($status == Report::STATUS_NOT_STARTED) {
            $qb->andWhere('r.reportStatusCached = :status')
                ->setParameter('status', $status);
        }

        return $qb;
    }
}
