<?php

namespace Daken\ReleaseProfilerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Error
 *
 * @ORM\Table(name="profiler_error",
 *      indexes={@ORM\Index(name="profiler_error_reference", columns={"reference"})}
 *     )
 * @ORM\Entity(repositoryClass="Daken\ReleaseProfilerBundle\Repository\ErrorRepository")
 */
class Error
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
     * @ORM\Column(name="error", type="text")
     */
    private $error;

    /**
     * @var string
     *
     * @ORM\Column(name="exception_class", type="string", length=255, nullable=true)
     */
    private $exceptionClass;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @var int
     *
     * @ORM\Column(name="lineNumber", type="integer", nullable=true)
     */
    private $lineNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="stackTrace", type="text", nullable=true)
     */
    private $stackTrace;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="float", nullable=false)
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity="Daken\ReleaseProfilerBundle\Entity\Request", inversedBy="errors")
     */
    private $request;

    public function __construct()
    {
        $this->reference = microtime(true);
        $this->created = new \DateTime();
    }

    public function __toString()
    {
        return $this->getError();
    }

    public static function fromException(\Exception $ex)
    {
        $error = new Error();
        $error->setError($ex->getMessage());
        $error->setFilename($ex->getFile());
        $error->setLineNumber($ex->getLine());
        $error->setStackTrace($ex->getTraceAsString());
        $error->setExceptionClass(get_class($ex));
        return $error;
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
     * Set error
     *
     * @param string $error
     *
     * @return Error
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Error
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set lineNumber
     *
     * @param integer $lineNumber
     *
     * @return Error
     */
    public function setLineNumber($lineNumber)
    {
        $this->lineNumber = $lineNumber;

        return $this;
    }

    /**
     * Get lineNumber
     *
     * @return int
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    /**
     * Set stackTrace
     *
     * @param string $stackTrace
     *
     * @return Error
     */
    public function setStackTrace($stackTrace)
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    /**
     * Get stackTrace
     *
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }

    /**
     * Set request
     *
     * @param \Daken\ReleaseProfilerBundle\Entity\Request $request
     *
     * @return Error
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
     * Set exceptionClass
     *
     * @param string $exceptionClass
     *
     * @return Error
     */
    public function setExceptionClass($exceptionClass)
    {
        $this->exceptionClass = $exceptionClass;

        return $this;
    }

    /**
     * Get exceptionClass
     *
     * @return string
     */
    public function getExceptionClass()
    {
        return $this->exceptionClass;
    }

    /**
     * Set reference
     *
     * @param float $reference
     *
     * @return Error
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return float
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Error
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
}
