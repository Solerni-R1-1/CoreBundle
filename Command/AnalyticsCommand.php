<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * Prepare the data for the analytics.
 */
class AnalyticsCommand extends ContainerAwareCommand
{
protected function configure() {
        parent::configure();
        $this->setName('claroline:analytics:prepare')
             ->setDescription('Prepare the analytics data set'); 
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
    	$output->writeln("Starting preparation of analytics queries...");
    	// Init the roles to filter the stats.
    	$roleManager = $this->getContainer()->get('claroline.manager.role_manager');
		$prepManager = $this->getContainer()->get('claroline.manager.analytics_preparation_manager');
		$wsRepo = $this->getContainer()->get('doctrine')->getRepository("ClarolineCoreBundle:Workspace\AbstractWorkspace");
		$wsArr = $wsRepo->findAll();
		
		foreach ($wsArr as $ws) {
			/* @var $ws AbstractWorkspace */
			if ($ws->getMooc() != null && count($ws->getMooc()->getMoocSessions()) > 0) {
				$output->writeln("Starting preparation of workspace ".$ws->getId()." with name ".$ws->getName());
				$excludeRoles = array();
				$managerRole = $roleManager->getManagerRole($ws);
				$excludeRoles[] = $managerRole->getName();
				$excludeRoles[] = "ROLE_ADMIN";
				$excludeRoles[] = "ROLE_WS_CREATOR";

				foreach ($ws->getMooc()->getMoocSessions() as $moocSession) {
					$output->writeln("Starting preparation of session ".$moocSession->getId()." with name ".$moocSession->getTitle());
					$prepManager->prepareConnectionsAndSubscriptionsByDay($moocSession, $excludeRoles);
					$output->writeln("Starting preparation of the users of the session ".$moocSession->getId()." with name ".$moocSession->getTitle());
					$prepManager->prepareUserAnalytics($moocSession, $excludeRoles);
					$output->writeln("Starting preparation of the badges of the session ".$moocSession->getId()." with name ".$moocSession->getTitle());
					$prepManager->prepareBadgeAnalytics($moocSession, $excludeRoles);
				}
				
				unset($excludeRoles);
				$output->writeln("Workspace ".$ws->getId()." with name ".$ws->getName()." done !");
			} else {
				//$output->writeln("Workspace ".$ws->getId()." with name ".$ws->getName()." doesn't have a mooc. Ignoring...");
			}
		}
		
		$output->writeln("Finished !");
    }
}
