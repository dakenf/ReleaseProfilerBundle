<?php

namespace Daken\ReleaseProfilerBundle\Tests\Entity;

use Daken\ReleaseProfilerBundle\Entity\DatabaseQuery;

class DatabaseQueryTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $q = new DatabaseQuery();
        $q->setQuery('qwe');
        $this->assertEquals((string)($q), 'qwe');

        $this->assertNotNull($q->getCreated());
        $this->assertNull($q->getId());
        $dt = new \DateTime();
        $q->setCreated($dt);
        $this->assertEquals($dt, $q->getCreated());
        $this->assertNull($q->getRequest());

        $q->setQuery(
            'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the'
        );
        $end = substr((string)$q, -3);
        $this->assertEquals($end, '...');
    }
}
