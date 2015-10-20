<?php

namespace AppBundle\Service\BruteForce;

use Predis\Client as PredisClient;

class AttemptsInTime
{

    const PREFIX = 'bf_';


    /**
     * @var PredisClient 
     */
    private $redis;

    /**
     * @var array 
     */
    private $triggers;


    public function __construct(PredisClient $redis, $key, array $triggers)
    {
        $this->redis = $redis;
        $this->key = $key;
        $this->triggers = $triggers;
    }


    public function maxAttemptsReached($currentTimestamp = null)
    {
        $currentTimestamp = (null === $currentTimestamp) ? time() : $currentTimestamp;

        $id = self::PREFIX . $this->key;
        $history = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        foreach ($this->triggers as $maxAttempts => $timeInterval) { // 3 60
            if ($this->countAttemptsInTheInterval($history, $currentTimestamp, $timeInterval) >= $maxAttempts) {
                return true;
            }
        }
        return false;
    }


    private function countAttemptsInTheInterval(array $history, $currentTimestamp, $timeInterval)
    {
        $ret = 0;
        foreach ($history as $attemptTimeStamp) {
            if ($attemptTimeStamp >= $currentTimestamp - $timeInterval) {
                $ret++;
            }
        }

        return $ret;
    }


    public function registerAttempt($timestamp = null)
    {
        $id = self::PREFIX . $this->key;
        $history = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        $history[] = (null === $timestamp ? time() : $timestamp);

        $this->redis->set($id, json_encode($history));

        return $this;
    }


    public function resetAttempts()
    {
        $id = self::PREFIX . $this->key;

        $this->redis->set($id, null);
    }

}