<?php

namespace Daken\ReleaseProfilerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Request
 *
 * @ORM\Table(name="profiler_request")
 * @ORM\Entity(repositoryClass="Daken\ReleaseProfilerBundle\Repository\RequestRepository")
 */
class Request
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
     * @ORM\Column(name="scheme", type="string", length=8)
     */
    private $scheme;

    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=255)
     */
    private $host;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text")
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text", nullable=true)
     */
    private $query;

    /**
     * @var string
     *
     * @ORM\Column(name="matched_route", type="string", length=255, nullable=true)
     */
    private $matchedRoute;

    /**
     * @var int
     *
     * @ORM\Column(name="time", type="integer")
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(name="request_method", type="string", length=8)
     */
    private $requestMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="request_body", type="blob", nullable=true)
     */
    private $requestBody;

    /**
     * @var int
     *
     * @ORM\Column(name="response_code", type="integer")
     */
    private $responseCode;

    /**
     * @var string
     *
     * @ORM\Column(name="response", type="blob", nullable=true)
     */
    private $response;

    /**
     * @var string
     *
     * @ORM\Column(name="client_ip", type="string", length=39)
     */
    private $clientIp;

    /**
     * @var string
     *
     * @ORM\Column(name="user_agent", type="text")
     */
    private $userAgent;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_database_query_time", type="integer")
     */
    private $totalDatabaseQueryTime;

    /**
     * @var string
     *
     * @ORM\Column(name="total_database_query_count", type="integer")
     */
    private $totalDatabaseQueryCount;

    private $createdMicroTime;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Daken\ReleaseProfilerBundle\Entity\DatabaseQuery", mappedBy="request", cascade={"all"}
     *     )
     */
    private $databaseQueries;

    /**
     * @ORM\OneToMany(targetEntity="Daken\ReleaseProfilerBundle\Entity\Error", mappedBy="request", cascade={"all"})
     */
    private $errors;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=true)
     */
    private $username;

    public function __construct()
    {
        $this->created = new \DateTime();

        $this->createdMicroTime = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->databaseQueries = new ArrayCollection();
        $this->errors = new ArrayCollection();
    }

    public static function fromHttpRequest(HttpRequest $request)
    {
        $instance = new Request();
        $instance->setClientIp($request->getClientIp());
        $instance->setHost($request->getHost());
        $instance->setPath($request->getPathInfo());
        $instance->setQuery($request->getQueryString());
        $instance->setRequestMethod($request->getMethod());
        $instance->setScheme($request->getScheme());
        $instance->setUserAgent($request->headers->get('User-Agent'));
        
        return $instance;
    }

    public function __toString()
    {
        return $this->getResponseCode().' '.$this->getRequestMethod().
        ' '.$this->getScheme().'://'.$this->getHost().$this->getPath();
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
     * @return Request
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
     * Set host
     *
     * @param string $host
     *
     * @return Request
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set url
     *
     * @param string $path
     *
     * @return Request
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set matchedRoute
     *
     * @param string $matchedRoute
     *
     * @return Request
     */
    public function setMatchedRoute($matchedRoute)
    {
        $this->matchedRoute = $matchedRoute;

        return $this;
    }

    /**
     * Get matchedRoute
     *
     * @return string
     */
    public function getMatchedRoute()
    {
        return $this->matchedRoute;
    }

    /**
     * Set time
     *
     * @param integer $time
     *
     * @return Request
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
     * Set requestMethod
     *
     * @param string $requestMethod
     *
     * @return Request
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    /**
     * Get requestMethod
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Set requestBody
     *
     * @param string $requestBody
     *
     * @return Request
     */
    public function setRequestBody($requestBody)
    {
        $this->requestBody = $requestBody;

        return $this;
    }

    /**
     * Get requestBody
     *
     * @return string
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * Set responseCode
     *
     * @param integer $responseCode
     *
     * @return Request
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Get responseCode
     *
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set response
     *
     * @param string $response
     *
     * @return Request
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response
     *
     * @return string|resource
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set clientIp
     *
     * @param string $clientIp
     *
     * @return Request
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * Get clientIp
     *
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * Set query
     *
     * @param string $query
     *
     * @return Request
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
     * Add databaseQuery
     *
     * @param \Daken\ReleaseProfilerBundle\Entity\DatabaseQuery $databaseQuery
     *
     * @return Request
     */
    public function addDatabaseQuery(DatabaseQuery $databaseQuery)
    {
        $this->databaseQueries[] = $databaseQuery;
        $databaseQuery->setRequest($this);

        return $this;
    }

    /**
     * Remove databaseQuery
     *
     * @param \Daken\ReleaseProfilerBundle\Entity\DatabaseQuery $databaseQuery
     */
    public function removeDatabaseQuery(DatabaseQuery $databaseQuery)
    {
        $this->databaseQueries->removeElement($databaseQuery);
    }

    /**
     * Get databaseQueries
     *
     * @return DatabaseQuery[]|\Doctrine\Common\Collections\Collection
     */
    public function getDatabaseQueries()
    {
        return $this->databaseQueries;
    }

    /**
     * Add error
     *
     * @param \Daken\ReleaseProfilerBundle\Entity\Error $error
     *
     * @return Request
     */
    public function addError(Error $error)
    {
        $this->errors[] = $error;
        $error->setRequest($this);

        return $this;
    }

    /**
     * Remove error
     *
     * @param \Daken\ReleaseProfilerBundle\Entity\Error $error
     */
    public function removeError(Error $error)
    {
        $this->errors->removeElement($error);
    }

    /**
     * Get errors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set schema
     *
     * @param string $scheme
     *
     * @return Request
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Get schema
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Set userAgent
     *
     * @param string $userAgent
     *
     * @return Request
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set totalDatabaseQueryTime
     *
     * @param integer $totalDatabaseQueryTime
     *
     * @return Request
     */
    public function setTotalDatabaseQueryTime($totalDatabaseQueryTime)
    {
        $this->totalDatabaseQueryTime = $totalDatabaseQueryTime;

        return $this;
    }

    /**
     * Get totalDatabaseQueryTime
     *
     * @return integer
     */
    public function getTotalDatabaseQueryTime()
    {
        return $this->totalDatabaseQueryTime;
    }

    /**
     * Set totalDatabaseQueryCount
     *
     * @param integer $totalDatabaseQueryCount
     *
     * @return Request
     */
    public function setTotalDatabaseQueryCount($totalDatabaseQueryCount)
    {
        $this->totalDatabaseQueryCount = $totalDatabaseQueryCount;

        return $this;
    }

    /**
     * Get totalDatabaseQueryCount
     *
     * @return integer
     */
    public function getTotalDatabaseQueryCount()
    {
        return $this->totalDatabaseQueryCount;
    }

    public function responseAsString()
    {
        return $this->getResponse() ? stream_get_contents($this->getResponse()) : null;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Request
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
}
