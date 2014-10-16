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
use Doctrine\ORM\EntityManager;

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
        $ctx = new \ZMQContext();
        $server = new \ZMQSocket($ctx, \ZMQ::SOCKET_PULL);
        $server->bind("tcp://*:11112");

        while (true) {
            $message = $server->recv();
            echo "**************************** Start : Using ".memory_get_usage()." bytes of memory !\n";
            $messageArray = json_decode($message, true);

            $before = new \DateTime();
            echo "Starting at : ".$before->format("Y-m-d  H:i:s")."\n";

            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->getConnection()->connect();
            echo "**************************** 1 : Using ".memory_get_usage()." bytes of memory !\n";
            // Extract data from message
            $userId = $messageArray['user'];
            $users = $messageArray['users'];
            $nbUsers = count($users);
            if (isset($messageArray['group'])) {
            	$groupId = $messageArray['group'];
            }
            if (isset($messageArray['total'])) {
            	$total = $messageArray['total'];
            } else {
            	$total = $nbUsers;
            }
            if (isset($messageArray['count'])) {
            	$count = $messageArray['count'];
            } else {
            	$count = $nbUsers;
            }
            
            if (isset($messageArray['action'])) {
            	$action = $messageArray['action'];
            } else {
            	$action = "import";// DEFAULT
            }
            echo "**************************** 2 : Using ".memory_get_usage()." bytes of memory !\n";
            // Get manager
            $userManager = $this->getContainer()->get('claroline.manager.user_manager');
            $groupManager = $this->getContainer()->get('claroline.manager.group_manager');
            $messageManager = $this->getContainer()->get('claroline.manager.message_manager');
            echo "**************************** 3 : Using ".memory_get_usage()." bytes of memory !\n";
            if ($action == "import") {
	            // Log console
	            $output->writeln("Importing $nbUsers users");
	            
	            // Import users
            	$userManager->importUsers($users);
            	echo "\n";
            } else {
            	$output->writeln("Batch of users already existing, skipping user creation...");
            }
            
            // Log console
            if (isset($groupId)) {
            	$output->writeln("Import done. Adding users to group");
            } else {
            	$output->writeln("Import done. Sending Message.");
            }
            echo "**************************** 4 : Using ".memory_get_usage()." bytes of memory !\n";
            
            $middle = new \DateTime();
            echo "Finished import at : ".$middle->format("Y-m-d  H:i:s").". Continuing...\n";

            // Get entities
            $user = $userManager->getUserById($userId);
            echo "**************************** 5 : Using ".memory_get_usage()." bytes of memory !\n";
            if (isset($groupId)) {
            	$group = $groupManager->getGroupById($groupId, false);
            }
            echo "**************************** 6 : Using ".memory_get_usage()." bytes of memory !\n";

            
            // Link users to group
            if (isset($group)) {
            	$groupManager->importUsers($group, $users);
	            // Log console
            	$output->writeln("Users added to group. Sending Message.");
            }
            echo "**************************** 7 : Using ".memory_get_usage()." bytes of memory !\n";
            
            // Send message to account who started the import
            unset($message);
            $message = $messageManager->create("L'import des $count/$total utilisateurs s'est bien déroulé.", "Résultat de votre import d'utilisateurs", array($user));
            $messageManager->send($message, false);
            
            $output->writeln("Message Sent.");
            $em->getConnection()->close();

            $after = new \DateTime();
            echo "Finished at : ".$after->format("Y-m-d  H:i:s")."\n";
            echo "Took ".($after->getTimestamp() - $before->getTimestamp())." seconds \n";
            echo "**************************** 8 : Using ".memory_get_usage()." bytes of memory !\n";
            
            unset($before);
            unset($middle);
            unset($after);
            unset($group);
            unset($message);
            unset($messageArray);
            unset($users);
            gc_collect_cycles();
            echo "**************************** End : Using ".memory_get_usage()." bytes of memory !\n";
            echo "\n\n\n\n";
        }
    }
}
