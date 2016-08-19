<?php

namespace Daken\ReleaseProfilerBundle\Logging;

use Daken\ReleaseProfilerBundle\Entity\DatabaseQuery;

class SQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    // 0KHQstC+0Y4g0LzQsNC80LrRgyDQt9Cw0LvQvtCz0LPQuNGA0YPQuQ==
    /** @var  \Doctrine\DBAL\Logging\SQLLogger */
    private $oldLogger;
    private $queries;
    private $queryCount = 0;

    private $logThreshold;
    private $totalTime = 0;

    /** @var  DatabaseQuery */
    private $currentQuery;

    public function __construct($logThreshold = 0)
    {
        $this->queries = [];
        $this->logThreshold = $logThreshold;
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->queryCount++;
        $this->currentQuery = new DatabaseQuery();
        $this->currentQuery->setQuery($sql);
        $this->currentQuery->setParameters(json_encode($params));
        $this->currentQuery->setStackTrace($this->getBacktrace());
        if ($this->oldLogger) {
            $this->oldLogger->startQuery($sql, $params, $types);
        }
    }

    public function getBacktrace()
    {
        $trace = debug_backtrace();
        array_shift($trace);
        array_shift($trace);
        $result = [];
        array_walk($trace, function ($a) use (&$result) {
            $str = $a['function'] . '()';
            if (isset($a['file'])) {
                $str .= ' at ' . basename($a['file']) . ":{$a['line']}";
            }

            if (!empty($a['class'])) {
                $str = $a['class'] . '->' . $str;
            }
            $result[] = $str;
        });

        return implode("\n", $result);
    }

    public function stopQuery()
    {
        $this->currentQuery->stop();
        $this->totalTime += $this->currentQuery->getTime();

        if ($this->currentQuery->getTime() > $this->logThreshold) {
            $this->queries[] = $this->currentQuery;
        }

        $this->currentQuery = null;
        if ($this->oldLogger) {
            $this->oldLogger->stopQuery();
        }
    }

    /**
     * @param \Doctrine\DBAL\Logging\SQLLogger $oldLogger
     */
    public function setOldLogger($oldLogger)
    {
        $this->oldLogger = $oldLogger;
    }

    /**
     * @return DatabaseQuery[]
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * @return int
     */
    public function getQueryCount()
    {
        return $this->queryCount;
    }

    /**
     * @return int
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }
}
