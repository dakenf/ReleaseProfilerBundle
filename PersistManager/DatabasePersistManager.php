<?php


namespace Daken\ReleaseProfilerBundle\PersistManager;

use Daken\ReleaseProfilerBundle\Entity\Request;
use Doctrine\ORM\EntityManagerInterface;

class DatabasePersistManager implements PersistManagerInterface
{
    /** @var EntityManagerInterface */
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function persist(Request $request)
    {
        $this->em->persist($request);
        $this->em->flush();
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->em;
    }
    
    public function getPendingRequest($blockTime = null)
    {
        return null;
    }
}
