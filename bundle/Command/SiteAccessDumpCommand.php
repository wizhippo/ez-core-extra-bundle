<?php

namespace Wizhippo\Bundle\EzCoreExtraBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteAccessDumpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wizhippo:siteaccess')
            ->setDescription('Dump siteaccess');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $siteAccess = $this->getContainer()->getParameter('ezpublish.siteaccess.default');

        $output->writeln(json_encode($siteAccess, JSON_PRETTY_PRINT));
    }
}
