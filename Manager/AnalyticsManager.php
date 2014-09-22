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
use Claroline\CoreBundle\Repository\Badge\BadgeRepository;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Controller\Badge\Tool\BadgeController;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Icap\DropzoneBundle\Entity\Drop;

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
    /** @var BadgeRepository */
    private $badgeRepository;
    /** @var BadgeManager */
    private $badgeManager;

    /**
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *     "moocService"   = @DI\Inject("orange.mooc.service"),
     *     "badgeManager"  = @DI\Inject("claroline.manager.badge")
     * })
     */
    public function __construct(ObjectManager $objectManager, MoocService $moocService, BadgeManager $badgeManager)
    {
        $this->om            	= $objectManager;
        $this->moocService 		= $moocService;
        $this->badgeManager		= $badgeManager;
        $this->resourceRepo  	= $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->resourceTypeRepo	= $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->userRepo      	= $objectManager->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo 	= $objectManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->logRepository 	= $objectManager->getRepository('ClarolineCoreBundle:Log\Log');
        $this->messageRepository= $objectManager->getRepository('ClarolineForumBundle:Message');
        $this->badgeRepository 	= $objectManager->getRepository('ClarolineCoreBundle:Badge\Badge');

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
        
        return array(
            'chartData' => $chartData,
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
    	// First last date is the beginning date
    	$lastDate = $from->format("Y-m-d");
    	// For each log
    	foreach ($logs as $i => $log) {
    		$currDate = $log->getDateLog()->format("Y-m-d");
    		// If we changed date
    		if ($lastDate != $currDate) {
    			// We need to put, in the array, the dates which have no logs. Starts here.
    			$lastDateTime = new \DateTime($lastDate);
    			$currentDateTime = new \DateTime($currDate);
	    		// Calculate the number of days between the last and the current log date
	    		$interval = $currentDateTime->diff($lastDateTime, true);
	    		$nbDays = $interval->format('%a');
	    		// For each days between the two, we complete the date which have no logs with default values
	    		// (0 for nbSubscriptions, the previous totalSubscriptions for the total Subscriptions) 
	    		for ($j = 0; $j < $nbDays - 1; $j++) {
	    			$index++;
	    			$lastDateTime = $lastDateTime->add(new \DateInterval("P1D"));
	    			$subscriptions[0][$index] = array();
	    			$subscriptions[0][$index][0] = $lastDateTime->format("Y-m-d");
	    			$subscriptions[0][$index][1] = $nbSubscriptions;
	    			$subscriptions[1][$index] = array();
	    			$subscriptions[1][$index][0] = $lastDateTime->format("Y-m-d");
	    			$subscriptions[1][$index][1] = 0;
	    		}
	    		// End here.
    			// For the log date, we initialize a new array in our array
    			$index ++;
    			$subscriptions[0][$index] = array();
    			$subscriptions[0][$index][0] = $currDate;
    			$subscriptions[1][$index] = array();
    			$subscriptions[1][$index][0] = $currDate;
    			$subscriptions[1][$index][1] = 0;
    		}
    		$nbSubscriptions++;
    		$subscriptions[0][$index][1] = $nbSubscriptions;
    		$subscriptions[1][$index][1]++;
    		$lastDate = $currDate;
    	}
    	$currDate = $to->format("Y-m-d");
    	if ($lastDate != $currDate) {
    		// We need to put, in the array, the dates which have no logs. Starts here.
    		$lastDateTime = new \DateTime($lastDate);
    		$currentDateTime = new \DateTime($currDate);
    		// Calculate the number of days between the last and the current log date
    		$interval = $currentDateTime->diff($lastDateTime, true);
    		$nbDays = $interval->format('%a');
    		// For each days between the two, we complete the date which have no logs with default values
    		// (0 for nbSubscriptions, the previous totalSubscriptions for the total Subscriptions)
    		for ($j = 0; $j < $nbDays; $j++) {
    			$index++;
    			$lastDateTime = $lastDateTime->add(new \DateInterval("P1D"));
    			$subscriptions[0][$index] = array();
    			$subscriptions[0][$index][0] = $lastDateTime->format("Y-m-d");
    			$subscriptions[0][$index][1] = $nbSubscriptions;
    			$subscriptions[1][$index] = array();
    			$subscriptions[1][$index][0] = $lastDateTime->format("Y-m-d");
    			$subscriptions[1][$index][1] = 0;
    		}
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
    	if ($workspace->isMooc()) {
    		$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    		if ($session != null && $session->getForum() != null) { 
		    	$forum = $session->getForum();
		    	$messages = $this->messageRepository->findAllPublicationsBetween($forum, $from, $to);
		    	
		    	$contributions = array();
		    	$contributions[0] = array();
		    	$nbContributions = 0;
		    	$nbDays = 0;
		    	
		    	// Extract data
		    	$index = -1;
		    	$lastDate = $to->format("Y-m-d");
		    	foreach ($messages as $i => $message) {
		    		$currDate = $message->getCreationDate()->format("Y-m-d");
		    		if ($lastDate != $currDate) {
			    		// We need to put, in the array, the dates which have no logs. Starts here.
			    		$lastDateTime = new \DateTime($lastDate);
			    		$currentDateTime = new \DateTime($currDate);
			    		// Calculate the number of days between the last and the current log date
			    		$interval = $lastDateTime->diff($currentDateTime, true);
			    		$diffDays = $interval->format('%a');
			    		// For each days between the two, we complete the date which have no logs with default values
			    		// (0 for nbSubscriptions, the previous totalSubscriptions for the total Subscriptions)
			    		for ($j = 0; $j < $diffDays - 1; $j++) {
			    			$index++;
			    			$nbDays++;
			    			$lastDateTime = $lastDateTime->sub(new \DateInterval("P1D"));
			    			$contributions[0][$index] = array();
			    			$contributions[0][$index][0] = $lastDateTime->format("Y-m-d");
			    			$contributions[0][$index][1] = 0;
			    		}
		    			$index ++;
		    			$contributions[0][$index] = array();
		    			$contributions[0][$index][0] = $currDate;
		    			$contributions[0][$index][1] = 0;
		    			$nbDays++;
		    		}
		    		$nbContributions++;
		    		$contributions[0][$index][1]++;
		    		$lastDate = $currDate;
		    	}
		    	$currDate = $from->format("Y-m-d");
		    	if ($lastDate != $currDate) {
		    		// We need to put, in the array, the dates which have no logs. Starts here.
		    		$lastDateTime = new \DateTime($lastDate);
		    		$currentDateTime = new \DateTime($currDate);
		    		// Calculate the number of days between the last and the current log date
		    		$interval = $lastDateTime->diff($currentDateTime, true);
		    		$diffDays = $interval->format('%a');
		    		// For each days between the two, we complete the date which have no logs with default values
		    		// (0 for nbSubscriptions, the previous totalSubscriptions for the total Subscriptions)
		    		for ($j = 0; $j < $diffDays; $j++) {
		    			$index++;
		    			$nbDays++;
		    			$lastDateTime = $lastDateTime->sub(new \DateInterval("P1D"));
		    			$contributions[0][$index] = array();
		    			$contributions[0][$index][0] = $lastDateTime->format("Y-m-d");
		    			$contributions[0][$index][1] = 0;
		    		}
		    	}
		    
		    	$contributions[1] = $nbDays > 0 ? $nbContributions / $nbDays : 0;
		    	
		    	return $contributions;
    		} else {
    			return null;
    		}
    	} else {
    		return null;
    	}
    }
    
    /**
     * Gives back the success rates of the various knowledge/skill badges (associated with quizzes)
     * @param AbstractWorkspace $workspace
     * @return Array of badges success rates [[badge1success, badge1failure],[badge2success][badge2failure],...]
     */
    public function getBadgesSuccessRate(AbstractWorkspace $workspace) {
    	$rates = array();
    	
    	if ($workspace->isMooc()) {
    		$workspaceUsers = array();
    		$mooc = $workspace->getMooc();
    		$sessions = $mooc->getMoocSessions();
    		
    		// Get all distinct users for all sessions 
    		foreach ($sessions as $session) {
    			/* @var $session MoocSession */
    			$users = $session->getUsers();
    			foreach ($users as $user) {
    				/* @var $user User */
    				if (!in_array($user, $workspaceUsers, true)) {
    					$workspaceUsers[] = $user;
    				}
    			}
    		}
    		
    		foreach ($workspaceUsers as $user) {
    			/* @var $user User */
    			$badges = $this->badgeManager->getAllBadgesForWorkspace($user, $workspace, true, true);
    			foreach ($badges as $badge) {
    				/* @var $badgeEntity Badge */
    				$badgeEntity = $badge['badge'];
    				$badgeName = $badgeEntity->getName();
    				
    				if (!array_key_exists($badgeName, $rates)) {
    					//echo ("=> ".$badgeName." <br />");
    					$rateBadge = array();
    					$rateBadge['success'] = 0;
    					$rateBadge['failure'] = 0;
    					$rateBadge['inProgress'] = 0;
    					$rateBadge['available'] = 0;
    					$rateBadge['name'] = $badgeName;
    					$rateBadge['type'] = ($badgeEntity->isKnowledgeBadge() ? "knowledge" : "skill");
    					$rates[$badgeName] = $rateBadge;
    				}/* else {
    					//echo ("<= ".$badgeName." <br />");
    					//$rateBadge = $rates[$badgeName];
    				}*/
    				
    				if ($badge['status'] == Badge::BADGE_STATUS_OWNED) {
    					$rates[$badgeName]['success']++;
    				} else if ($badge['status'] == Badge::BADGE_STATUS_FAILED) {
    					$rates[$badgeName]['failure']++;
    				} else if ($badge['status'] == Badge::BADGE_STATUS_IN_PROGRESS) {
    					$rates[$badgeName]['inProgress']++;
    				} else if ($badge['status'] == Badge::BADGE_STATUS_AVAILABLE) {
    					$rates[$badgeName]['available']++;
    				}
    				//echo ($rateBadge['name']." success = ".$rateBadge['success']."<br />");
    			}
    		}
    		
    		
    	}
    	
    	return $rates;
    }
    
    public function getForumStats(AbstractWorkspace $workspace, \DateTime $from, \DateTime $to) {
    	$result = array();
    	$userRows = array();
    	if ($workspace->isMooc()) {
    		$workspaceUsers = array();
    		$mooc = $workspace->getMooc();
    			
    		$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    		if ($session != null && $session->getForum() != null) {
    			$users = $session->getUsers();
    			
    			// Extract data
    			foreach ($users as $user) {
    				// Get information from database
    				$nbPublications = $this->messageRepository->countMessagesForUser($session->getForum(), $user, $from, $to);
    	
    				$userRow = array();
    				$userRow["lastname"] = $user->getLastName();
    				$userRow["firstname"] = $user->getFirstName();
    				$userRow["username"] = $user->getUsername();
    				$userRow["mail"] = $user->getMail();
    				$userRow["nbPublications"] = $nbPublications;
    	
    				if (!isset($userRows[$nbPublications])) {
    					$userRows[$nbPublications] = array();
    				}
    				$userRows[$nbPublications][] = $userRow;
    			}
    		}
    	}
    	krsort($userRows);
    	foreach ($userRows as $nbPublication => $userRowSet) {
    		foreach($userRowSet as $userRow) {
    			$result[] = $userRow;
    		}
    	}
    	
    	return $result;
    }
    
}
