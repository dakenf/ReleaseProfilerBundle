<?php

namespace Daken\ReleaseProfilerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FlushRequestsToDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("daken:profiler:flush")
            ->setDescription("Flushes request data from message queue to database")
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Runs command as daemon')
            ->addOption('wait-seconds', 'ws', InputOption::VALUE_REQUIRED, 'Wait seconds for redis blocking request')
            ->addOption('silent', null, InputOption::VALUE_NONE, 'Silent mode')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $databaseManager = $this->getContainer()->get('daken_release_profiler.persist_manager.database');
        $currentManager = $this->getContainer()->get('daken_release_profiler.persist_manager');
        $em = $databaseManager->getEntityManager();

        $waitSeconds = $input->getOption('wait-seconds') ?: 20;
        if (!$input->getOption('daemon')) {
            $waitSeconds = null;
        }

        $silent = $input->getOption('silent');
        while ($req = $currentManager->getPendingRequest($waitSeconds) or $waitSeconds) {
            if ($req) {
                $databaseManager->persist($req);
                if (!$silent) {
                    $output->writeln("Persisted request " . $req);
                }
            }
            $em->clear();
        }
    }
}
