<?php

namespace Daken\ReleaseProfilerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DatabaseQuery
 *
 * @ORM\Table(name="profiler_database_query")
 * @ORM\Entity(repositoryClass="Daken\ReleaseProfilerBundle\Repository\DatabaseQueryRepository")
 */
class DatabaseQuery
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text")
     */
    private $query;

    /**
     * @var string
     *
     * @ORM\Column(name="parameters", type="text", nullable=true)
     */
    private $parameters;

    /**
     * @var string
     *
     * @ORM\Column(name="stack_trace", type="text")
     */
    private $stackTrace;

    /**
     * @var int
     *
     * @ORM\Column(name="time", type="integer")
     */
    private $time;

    /**
     * @ORM\ManyToOne(targetEntity="Daken\ReleaseProfilerBundle\Entity\Request", inversedBy="databaseQueries")
     */
    private $request;

    private $createdMicroTime;

    public function __construct()
    {
        $this->createdMicroTime = microtime(true);
        $this->created = new \DateTime();
    }

    public function __toString()
    {
        if (strlen($this->query) < 75) {
            return $this->query;
        }

        return substr($this->query, 0, 73).'...';
    }

    public function stop()
    {
        $this->setTime((microtime(true) - $this->createdMicroTime) * 1000);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return DatabaseQuery
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set query
     *
     * @param string $query
     *
     * @return DatabaseQuery
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set parameters
     *
     * @param string $parameters
     *
     * @return DatabaseQuery
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set time
     *
     * @param integer $time
     *
     * @return DatabaseQuery
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set request
     *
     * @param \Daken\ReleaseProfilerBundle\Entity\Request $request
     *
     * @return DatabaseQuery
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return \Daken\ReleaseProfilerBundle\Entity\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set backtrace
     *
     * @param string $stackTrace
     *
     * @return DatabaseQuery
     */
    public function setStackTrace($stackTrace)
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    /**
     * Get backtrace
     *
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }
}
