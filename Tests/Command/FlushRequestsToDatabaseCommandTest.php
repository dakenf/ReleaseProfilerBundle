<?php

namespace Daken\ReleaseProfilerBundle\Tests\Command;

use Daken\ReleaseProfilerBundle\Command\FlushRequestsToDatabaseCommand;
use Daken\ReleaseProfilerBundle\Entity\Request;
use Daken\ReleaseProfilerBundle\PersistManager\DatabasePersistManager;
use Daken\ReleaseProfilerBundle\Tests\PersistManager\TestPersistManager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FlushRequestsToDatabaseCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCommand()
    {
        $request = new Request();
        $sourceManager = new TestPersistManager();
        $sourceManager->persist($request);
        $container = new ContainerBuilder();
        $container->set(
            'daken_release_profiler.persist_manager',
            $sourceManager
        );

        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $em
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->equalTo($request));
        $em
            ->expects($this->exactly(2))
            ->method('flush')
            ->with($this->equalTo($request));

        $container->set(
            'daken_release_profiler.persist_manager.database',
            new DatabasePersistManager($em)
        );

        $command = new FlushRequestsToDatabaseCommand();
        $command->setContainer($container);
        $command->getDefinition();

        $input = new StringInput('--silent');
        $output = new NullOutput();
        $command->run($input, $output);

        $sourceManager->persist($request);
        $input = new StringInput('');
        $command->run($input, $output);
    }
}
