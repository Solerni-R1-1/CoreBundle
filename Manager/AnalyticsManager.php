<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Event\Log\LogResourceExportEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\ForumBundle\Repository\MessageRepository;
use Claroline\CoreBundle\Controller\Mooc\MoocService;

/**
 * @DI\Service("claroline.manager.analytics_manager")
 */
class AnalyticsManager
{
    /** @var AbstractResourceRepository */
    private $resourceRepo;
    /** @var AbstractResourceRepository */
    private $resourceTypeRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;
    /** @var LogRepository */
    private $logRepository;
    /** @var MessageRepository */
    private $messageRepository;
    /** @var MoocService */
    private $moocService;

    /**
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *     "moocService"   = @DI\Inject("orange.mooc.service")
     * })
     */
    public function __construct(ObjectManager $objectManager, MoocService $moocService)
    {
        $this->om            	= $objectManager;
        $this->moocService 		= $moocService;
        $this->resourceRepo  	= $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->resourceTypeRepo	= $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->userRepo      	= $objectManager->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo 	= $objectManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->logRepository 	= $objectManager->getRepository('ClarolineCoreBundle:Log\Log');
        $this->messageRepository= $objectManager->getRepository('ClarolineForumBundle:Message');

    }

    public function getDefaultRange()
    {
        //By default last thirty days :
        $startDate = new \DateTime('now');
        $startDate->setTime(0, 0, 0);
        $startDate->sub(new \DateInterval('P29D')); // P29D means a period of 29 days

        $endDate = new \DateTime('now');
        $endDate->setTime(23, 59, 59);

        return array($startDate->getTimestamp(), $endDate->getTimestamp());
    }

    public function getYesterdayRange()
    {
        //By default last thirty days :
        $startDate = new \DateTime('now');
        $startDate->setTime(0, 0, 0);
        $startDate->sub(new \DateInterval('P1D')); // P1D means a period of 1 days

        $endDate = new \DateTime('now');
        $endDate->setTime(23, 59, 59);
        $endDate->sub(new \DateInterval('P1D')); // P1D means a period of 1 days

        return array($startDate->getTimestamp(), $endDate->getTimestamp());
    }

    public function getDailyActionNumberForDateRange(
        $range = null,
        $action = null,
        $unique = false,
        $workspaceIds = null
    )
    {
        if ($action === null) {
            $action = '';
        }

        if ($range === null) {
            $range = $this->getDefaultRange();
        }

        $userSearch = null;
        $actionRestriction = null;
        $chartData = $this->logRepository->countByDayFilteredLogs(
            $action,
            $range,
            $userSearch,
            $actionRestriction,
            $workspaceIds,
            $unique
        );

        return $chartData;
    }

    public function getTopByCriteria($range = null, $topType = null, $max = 30)
    {
        if ($topType == null) {
            $topType = 'top_users_connections';
        }
        $listData = array();

        switch ($topType) {
            case 'top_extension':
                $listData = $this->resourceRepo->findMimeTypesWithMostResources($max);
                break;
            case 'top_workspaces_resources':
                $listData = $this->workspaceRepo->findWorkspacesWithMostResources($max);
                break;
            case 'top_workspaces_connections':
                $listData = $this->topWSByAction($range, LogWorkspaceToolReadEvent::ACTION, $max);
                break;
            case 'top_resources_views':
                $listData = $this->topResourcesByAction($range, LogResourceReadEvent::ACTION, $max);
                break;
            case 'top_resources_downloads':
                $listData = $this->topResourcesByAction($range, LogResourceExportEvent::ACTION, $max);
                break;
            case 'top_users_connections':
                $listData = $this->topUsersByAction($range, LogUserLoginEvent::ACTION, $max);
                break;
            case 'top_users_workspaces_enrolled':
                $listData = $this->userRepo->findUsersEnrolledInMostWorkspaces($max);
                break;
            case 'top_users_workspaces_owners':
                $listData = $this->userRepo->findUsersOwnersOfMostWorkspaces($max);
                break;
            case 'top_media_views':
                $listData = $this->topMediaByAction($range, LogResourceReadEvent::ACTION, $max);
                break;
        }

        return $listData;
    }

    public function topWSByAction($range = null, $action = null, $max = -1)
    {
        if ($range === null) {
            $range = $this->getYesterdayRange();
        }

        if ($action === null) {
            $action = LogWorkspaceToolReadEvent::ACTION;
        }

        $resultData = $this->logRepository->topWSByAction($range, $action, $max);

        return $resultData;
    }

    public function topMediaByAction($range = null, $action = null, $max = -1)
    {
        if ($range === null) {
            $range = $this->getYesterdayRange();
        }

        if ($action === null) {
            $action = LogResourceReadEvent::ACTION;
        }

        $resultData = $this->logRepository->topMediaByAction($range, $action, $max);

        return $resultData;
    }

    public function topResourcesByAction($range = null, $action = null, $max = -1)
    {
        if ($range === null) {
            $range = $this->getYesterdayRange();
        }

        if ($action === null) {
            $action = LogResourceReadEvent::ACTION;
        }

        $resultData = $this->logRepository->topResourcesByAction($range, $action, $max);

        return $resultData;
    }

    public function topUsersByAction($range = null, $action = null, $max = -1)
    {
        if ($range === null) {
            $range = $this->getYesterdayRange();
        }

        if ($action === null) {
            $action = LogUserLoginEvent::ACTION;
        }

        $resultData = $this->logRepository->topUsersByAction($range, $action, $max);

        return $resultData;
    }

    /**
     * Retrieve user who connected at least one time on the application
     *
     * @return mixed
     */
    public function getActiveUsers()
    {
        $resultData = $this->logRepository->activeUsers();

        return $resultData;
    }

    /**
     * Retrieve analytics for workspace: chartData and resource statistics
     */
    public function getWorkspaceAnalytics(AbstractWorkspace $workspace)
    {
        $range = $this->getDefaultRange();
        $action = 'workspace-enter';
        $workspaceIds = array($workspace->getId());
        $chartData = $this->getDailyActionNumberForDateRange($range, $action, false, $workspaceIds);
        $resourcesByType = $this->resourceTypeRepo->countResourcesByType($workspace);
        $hourlyAudience = $this->getHourlyAudience($workspace);
        $activeUsers = $this->getPercentageActiveMembers($workspace);
        
        $defaultFrom = new \DateTime();
        $defaultFrom->sub(new \DateInterval("P10M"));
        $subscriptionStats = $this->getSubscriptionsForPeriod($workspace, $defaultFrom, new \DateTime());
        $forumContributions = $this->getForumActivity($workspace, $defaultFrom, new \DateTime());

        return array('chartData' => $chartData,
        	'hourlyAudience' => $hourlyAudience,
        	'activeUsers' => $activeUsers,
        	'forumContributions' => $forumContributions,
        	'subscriptionStats' => $subscriptionStats,
            'resourceCount' => $resourcesByType,
            'workspace' => $workspace
        );
    }
    
    /**
     * Gets the hourly audience for the workspace.
     * @param AbstractWorkspace $workspace 
     * 
     * @return An array containing two arrays :
     *    - The first is the workspace connections for every hour of the day
     *    - The second is the workspace associated logs (which are not connections) for every hour of the day
     */
    public function getHourlyAudience(AbstractWorkspace $workspace) {
    	$audience = array();
    	$audience[] = array();
    	$audience[] = array();
    	
    	$logs = $this->logRepository->findBy(array(
    			"workspace" => $workspace
    	));
    	for ($i = 1; $i < 25; $i++) {
    		$audience[0][$i] = 0;
    		$audience[1][$i] = 0;
    	}
    	foreach ($logs as $i => $log) {
    		$hour = intval($log->getDateLog()->format('H'));
    		if ($log->getAction() == "workspace-enter") {
    			$audience[0][$hour]++;
    		} else {
    			$audience[1][$hour]++;
    		}
    	}
    	
    	return $audience;
    }
    
    /**
     * Sends back the total subscription of a workspace every day 
     * and the number of subscriptions to this workspace each day
     * 
     * @param AbstractWorkspace $workspace
     * @param \DateTime $from
     * @param \DateTime $to
     */
    public function getSubscriptionsForPeriod(AbstractWorkspace $workspace, \DateTime $from, \DateTime $to) {
    	$subscriptions = array();
    	$subscriptions[] = array();
    	$subscriptions[] = array();

    	// Get information from database
    	$logs = $this->logRepository->findAllBetween($workspace, $from, $to, "workspace-role-subscribe_user");
    	$nbSubscriptions = $this->logRepository->getSubscribeCountUntil($workspace, $from);

    	// Extract data
    	$index = -1;
    	$lastDate = null;
    	foreach ($logs as $i => $log) {
    		if ($lastDate != $log->getDateLog()->format("Y-m-d")) {
    			$index ++;
    			$subscriptions[0][$index] = array();
    			$subscriptions[0][$index][0] = $log->getDateLog()->format("Y-m-d");
    			$subscriptions[1][$index] = array();
    			$subscriptions[1][$index][0] = $log->getDateLog()->format("Y-m-d");
    			$subscriptions[1][$index][1] = 0;
    		}
    		$nbSubscriptions++;
    		$subscriptions[0][$index][1] = $nbSubscriptions;
    		$subscriptions[1][$index][1]++;
    		$lastDate = $log->getDateLog()->format("Y-m-d");
    	}
    	
    	return $subscriptions;
    	
    } 
    
    /**
     * Gives back the number of active members since N days and the total of members of this workspace.
     * [active, total]
     * 
     * @param AbstractWorkspace $workspace
     * @param number $nbDays
     * @return number
     */
    public function getPercentageActiveMembers(AbstractWorkspace $workspace, $nbDays = 5) {
    	$date = new \DateTime();
    	$date->sub(new \DateInterval("P".$nbDays."D"));
    	
    	$nbActive = $this->logRepository->countActiveUsersSinceDate($workspace, $date)[0][1];
    	$nbTotal =  $this->logRepository->countRegisteredUsers($workspace)[0][1];
    	return [$nbActive , $nbTotal];
    }
    
    /**
     * 
     * @param AbstractWorkspace $workspace
     */
    public function getForumActivity(AbstractWorkspace $workspace, \DateTime $from, \DateTime $to) {
    	$forum = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace)->getForum();
    	$messages = $this->messageRepository->findAllPublicationsBetween($forum, $from, $to);
    	
    	$contributions = array();
    	$contributions[0] = array();
    	$nbContributions = 0;
    	$nbDays = 0;
    	
    	
    	// Extract data
    	$index = -1;
    	$lastDate = null;
    	foreach ($messages as $i => $message) {
    		if ($lastDate != $message->getCreationDate()->format("Y-m-d")) {
    			$index ++;
    			$contributions[0][$index] = array();
    			$contributions[0][$index][0] = $message->getCreationDate()->format("Y-m-d");
    			$contributions[0][$index][1] = 0;
    			$nbDays++;
    		}
    		$nbContributions++;
    		$contributions[0][$index][1]++;
    		$lastDate = $message->getCreationDate()->format("Y-m-d");
    	}
    	
    	$contributions[1] = $nbContributions / $nbDays;
    	
    	return $contributions;
    }
    
    /**
     * Gives back the success rates of the various knowledge badges (associated with quizzes)
     * @param AbstractWorkspace $workspace
     * @return Array of badges success rates [[badge1success, badge1failure],[badge2success][badge2failure],...]
     */
    public function getKnowledgeBadgesSuccessRate(AbstractWorkspace $workspace) {
    	$rates = array();
    	
    	
    	
    	return $rates;
    }
}
