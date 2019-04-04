<?php

namespace AppBundle\Model\Stats;

class StatsQueryResponse
{
    private $paNamedDeputyCount;

    private $profNamedDeputyCount;

    private $reportsCount;

    public function setPaNamedDeputyCount($paNamedDeputyCount)
    {
        $this->paNamedDeputyCount = $paNamedDeputyCount;
    }

    public function getPaNamedDeputyCount()
    {
        return $this->paNamedDeputyCount;
    }

    public function setProfNamedDeputyCount($ProfNamedDeputyCount)
    {
        $this->profNamedDeputyCount = $ProfNamedDeputyCount;
    }

    public function getProfNamedDeputyCount()
    {
        return $this->profNamedDeputyCount;
    }

    public function setReportsCount($reportsCount)
    {
        $this->reportsCount = $reportsCount;
    }

    public function getReportsCount()
    {
        return $this->reportsCount;
    }

    public function toArray()
    {
        return [
            'prof_named_deputy_count' => $this->getProfNamedDeputyCount,
            'pa_named_deputy_count' => $this->getPaNamedDeputyCount,
            'reports_count' => $this->getReportsCount,
        ];
    }
}