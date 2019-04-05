<?php

namespace AppBundle\Model\Stats;

class StatsQueryResponse
{
    private $paNamedDeputyCount;

    private $profNamedDeputyCount;

    private $reportsCount;
    
    private $from;
    
    private $to;

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

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }
    
    public function toArray()
    {
        return [
            'prof_named_deputy_count' => $this->getProfNamedDeputyCount(),
            'pa_named_deputy_count' => $this->getPaNamedDeputyCount(),
            'reports_count' => $this->getReportsCount(),
            'from' => $this->getFrom(),
            'to' => $this->getTo()
        ];
    }
}
