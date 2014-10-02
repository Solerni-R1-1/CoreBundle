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

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class ImportCommand extends ContainerAwareCommand
{
protected function configure()
    {
        parent::configure();
        $this->setName('claroline:import:watch')
             ->setDescription('Wait for entities to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    	$em = $this->getContainer()->get('doctrine')->getEntityManager();
    	$em->getConnection()->close();
        $ctx = new \ZMQContext();
        $server = new \ZMQSocket($ctx, \ZMQ::SOCKET_PULL);
        $server->bind("tcp://*:11112");

        while (true) {
            $message = $server->recv();
            $messageArray = json_decode($message, true);

            $em = $this->getContainer()->get('doctrine')->getEntityManager();
            $em->getConnection()->connect();
            // Extract data from message
            $userId = $messageArray['user'];
            $users = $messageArray['users'];
            $nbUsers = count($users);
            if (isset($messageArray['group'])) {
            	$groupId = $messageArray['group'];
            }
            
            // Get manager
            $userManager = $this->getContainer()->get('claroline.manager.user_manager');
            $groupManager = $this->getContainer()->get('claroline.manager.group_manager');
            $messageManager = $this->getContainer()->get('claroline.manager.message_manager');
            
            // Log console
            $output->writeln("Importing $nbUsers users");
            
            // Import users
            $userManager->importUsers($users);
            
            // Log console
            if (isset($groupId)) {
            	$output->writeln("Import done. Adding users to group");
            } else {
            	$output->writeln("Import done. Sending Message.");
            }


            // Get entities
            $user = $userManager->getUserById($userId);
            if (isset($groupId)) {
            	$group = $groupManager->getGroupById($groupId);
            }
            
            // Link users to group
            if (isset($group)) {
            	$groupManager->importUsers($group, $users);
	            // Log console
            	$output->writeln("Users added to group. Sending Message.");
            }
            
            // Send message to account who started the import
            $message = $messageManager->create("L'import des $nbUsers utilisateurs s'est bien déroulé.", "Résultat de votre import d'utilisateurs", array($user));
            $messageManager->send($message, false);
            
            $output->writeln("Message Sent.");
            $em->getConnection()->close();
        }
    }
}
