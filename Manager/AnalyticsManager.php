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
use Claroline\ForumBundle\Entity\Message;
use Symfony\Component\Translation\TranslatorInterface;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Group;

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
    private $translator;
    
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *     "moocService"   = @DI\Inject("orange.mooc.service"),
     *     "badgeManager"  = @DI\Inject("claroline.manager.badge"),
     *     "translator"    = @DI\Inject("translator"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
    		ObjectManager $objectManager,
    		MoocService $moocService,
    		BadgeManager $badgeManager,
    		TranslatorInterface $translator,
    		RoleManager $roleManager)
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
        $this->translator       = $translator;
        $this->roleManager		= $roleManager;

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
    public function getHourlyAudience(AbstractWorkspace $workspace, $filteredRoles) {
    	$audience = array();
    	$audience[] = array();
    	$audience[] = array();
    	
    	$logs = $this->logRepository->findBy(array(
    			"workspace" => $workspace
    	));
    	for ($i = 0; $i < 24; $i++) {
    		$audience[0][$i] = 0;
    		$audience[1][$i] = 0;
    	}
    	foreach ($logs as $i => $log) {
    		$user = $log->getDoer();
    		if ($user != null) { // If user doesn't exists, it is anonymous. Let anonymous in the stats...
	    		foreach ($user->getRoles() as $role) {
	    			if (in_array($role, $filteredRoles)) {
	    				continue 2;
	    			}
	    		}
    		}
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
    public function getSubscriptionsForPeriod(AbstractWorkspace $workspace, \DateTime $from, \DateTime $to, $filteredRoles) {
    	$subscriptions = array();
    	$subscriptions[] = array();
    	$subscriptions[] = array();

    	// Get information from database
    	$logs = $this->logRepository->findAllBetween(
    			$workspace,
    			$from,
    			$to,
    			array(
    				"workspace-role-subscribe_user",	
    				"workspace-role-subscribe_group",
    				"workspace-role-unsubscribe_user",
    				"workspace-role-unsubscribe_group"),
    			$filteredRoles);
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
    		if ($log->getAction() == "workspace-role-subscribe_user") {
    			$step = 1;
    		} else if ($log->getAction() == "workspace-role-subscribe_group") {
    			$step = count($log->getReceiverGroup()->getUsers());
    		} else if ($log->getAction() == "workspace-role-unsubscribe_user") { 
    			$step = -1;
    		} else if ($log->getAction() == "workspace-role-unsubscribe_group") {
    			$step = -count($log->getReceiverGroup()->getUsers());
    		}
    		$nbSubscriptions+= $step;
    		$subscriptions[0][$index][1] = $nbSubscriptions;
    		$subscriptions[1][$index][1]+= $step;
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
    public function getPercentageActiveMembers(AbstractWorkspace $workspace, $nbDays = 5, $filteredRoles) {
    	$date = new \DateTime("today midnight");
    	$date->sub(new \DateInterval("P".$nbDays."D"));
    	
    	$nbActive = $this->logRepository->countActiveUsersSinceDate($workspace, $date, $filteredRoles);
    	$nbActive += $this->logRepository->countActiveGroupsUsersSinceDate($workspace, $date, $filteredRoles);
    	$nbTotal = count($workspace->getAllUsers($filteredRoles));
    	
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
		    	$managerRole = $this->roleManager->getManagerRole($workspace);
		    	$messages = $this->messageRepository->findAllPublicationsBetween(
		    			$forum,
		    			$from,
		    			$to,
		    			array("ROLE_ADMIN", $managerRole->getName()));
		    	
		    	$contributions = array();
		    	$contributions[0] = array();
		    	$nbContributions = 0;
		    	$nbDays = 0;
		    	
		    	// Extract data
		    	$index = -1;
		    	$lastDate = $to->format("Y-m-d");
		    	foreach ($messages as $i => $message) {
		    		/* @var $message Message */
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
    public function getBadgesSuccessRate(AbstractWorkspace $workspace, $filteredRoles) {
    	$rates = array();
    	
    	if ($workspace->isMooc()) {
    		$workspaceUsers = array();
    		$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    		
    		$users = $session->getAllUsers($filteredRoles);
    		
    		foreach ($users as $user) {
    			/* @var $user User */
    			$badges = $this->badgeManager->getAllBadgesForWorkspace($user, $workspace, true, true);
    			foreach ($badges as $badge) {
    				/* @var $badgeEntity Badge */
    				$badgeEntity = $badge['badge'];
    				$badgeName = $badgeEntity->getName();
    				
    				if (!array_key_exists($badgeName, $rates)) {
    					$rateBadge = array();
    					$rateBadge['success'] = 0;
    					$rateBadge['failure'] = 0;
    					$rateBadge['inProgress'] = 0;
    					$rateBadge['available'] = 0;
    					$rateBadge['name'] = $badgeName;
    					$rateBadge['id'] = $badgeEntity->getId();
    					$rateBadge['type'] = ($badgeEntity->isKnowledgeBadge() ? "knowledge" : "skill");
    					$rates[$badgeName] = $rateBadge;
    				}
    				
    				if ($badge['status'] == Badge::BADGE_STATUS_OWNED) {
    					$rates[$badgeName]['success']++;
    				} else if ($badge['status'] == Badge::BADGE_STATUS_FAILED) {
    					$rates[$badgeName]['failure']++;
    				} else if ($badge['status'] == Badge::BADGE_STATUS_IN_PROGRESS) {
    					$rates[$badgeName]['inProgress']++;
    				} else if ($badge['status'] == Badge::BADGE_STATUS_AVAILABLE) {
    					$rates[$badgeName]['available']	++;
    				}
    			}
    		}
    		
    		
    	}
    	
    	return $rates;
    }
    
    public function getForumStats(AbstractWorkspace $workspace, \DateTime $from, \DateTime $to, $filteredRoles) {
    	$result = array();
    	$userRows = array();
    	if ($workspace->isMooc()) {
    		$workspaceUsers = array();
    		$mooc = $workspace->getMooc();
    			
    		$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    		if ($session != null && $session->getForum() != null) {
    			$users = $session->getAllUsers($filteredRoles);
    			
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
    
    public function getBadgesParticitpationRate(AbstractWorkspace $workspace, $filteredRoles) {
    	$result = array();
    	
    	$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    	$users = $session->getAllUsers($filteredRoles);
    	$nbUsers = count($users);
    	// This array will contains, for each badge, the list of users and associated badge.
    	// $badges[badgeId][0..*]['user'] = UserEntity
    	// $badges[badgeId][0..*]['badge'] = UserBadge (containing the badge, the resource, the status, etc.)
    	$badges = array();
    	
    	foreach ($users as $user) {
    		$userBadges = $this->badgeManager->getAllBadgesForWorkspace($user, $workspace, true, true);
    		
    		foreach ($userBadges as $userBadge) {
    			$badgeId = $userBadge['badge']->getId();
    			if (!isset($badges[$badgeId])) {
    				$badges[$badgeId] = array();
    				$resultArray = array();
    				$resultArray['badge'] = $userBadge['badge']->getName();
    				$resultArray['data'] = array();
    				$result[$badgeId] = $resultArray;
    			}
    			
    			$badgeAndUser = array();
    			$badgeAndUser['badge'] = $userBadge;
    			$badgeAndUser['user'] = $user;
    			
    			$badges[$badgeId][] = $badgeAndUser;
    		}
    	}
    	
    	// We then start putting data in result array.
    	// The result array will look like :
    	// $result[badgeId]
    	//		=> ['badge'] : The badge entity
    	//		=> ['data']
    	//				=> ['percentage'][DateString (Y-m-d)]
    	//						=> [0] = DateString (Y-m-d)
    	//						=> [1] = value
    	//				=> ['total'][DateString (Y-m-d)]
    	//						=> [0] = DateString (Y-m-d)
    	//						=> [1] = value
    	//				=> ['count'][DateString (Y-m-d)]
    	//						=> [0] = DateString (Y-m-d)
    	//						=> [1] = value
    	foreach ($badges as $badgeId => $badgeUsers) {
    		$data = &$result[$badgeId]['data'];
    		$totalData = array();
    		$percentageData = array();
    		$countData = array();
    		foreach ($badgeUsers as $badgeUser) {
    			$user = $badgeUser['user'];
    			$userBadge = $badgeUser['badge'];
    			
    			if ($userBadge['badge']->isSkillBadge()) {
	    			if (isset($userBadge['resource']['resource']['drop']) 
	    					&& $userBadge['resource']['resource']['drop'] != null) {
	    				$startDate = $userBadge['resource']['resource']['drop']->getDropDate()->format('Y-m-d');
	    			
		    			if (!isset($countData[$startDate])) {
		    				$countData[$startDate] = array();
		    				$countData[$startDate][0] = $startDate;
		    				$countData[$startDate][1] = 0;
		    			}
		    			$countData[$startDate][1]++;
	    			}
    			} else {
    				if (isset($userBadge['resource']['resource']['firstAttempt'])
	    					&& $userBadge['resource']['resource']['firstAttempt'] != null) {
    					$startDate = $userBadge['resource']['resource']['firstAttempt']->getStart()->format('Y-m-d');
    					if (!isset($countData[$startDate])) {
    						$countData[$startDate] = array();
    						$countData[$startDate][0] = $startDate;
    						$countData[$startDate][1] = 0;
    					}
    					$countData[$startDate][1]++;
	    			}
    			}
    		}
    		$data['percentage'] = $percentageData;
    		$data['total'] = $totalData;
    		$data['count'] = $countData;
    	}
    	
    	// We now need to complete the data with the missing dates
    	$from = $session->getStartDate();
    	$to = $session->getEndDate();
    	$previousDateString = null;
    	
    	$now = new \DateTime();
    	if ($now < $to) {
    		$to = $now;
    	}
    	
    	$nbDays = $from->diff($to, true)->format('%a');
    	
    	for ($i = 0; $i < $nbDays; $i++) {
    		$currDateString = $from->format('Y-m-d');
	    	foreach ($result as &$badgeResult) {
	    		$data = &$badgeResult['data'];
	    		$totalData = &$data['total'];
	    		$percentageData = &$data['percentage'];
	    		$countData = &$data['count'];
	    		
	    		if (!isset($countData[$currDateString])) {
	    			$countData[$currDateString] = array();
	    			$countData[$currDateString][0] = $currDateString;
	    			$countData[$currDateString][1] = 0;
	    		}
	    		$percentageData[$currDateString] = array();
	    		$percentageData[$currDateString][0] = $currDateString;
	    		$totalData[$currDateString] = array();
	    		$totalData[$currDateString][0] = $currDateString;
	    		
	    		if ($previousDateString == null) {
	    			$totalData[$currDateString][1] = 0;
	    			$percentageData[$currDateString][1] = 0;
	    		} else {
	    			$totalData[$currDateString][1] = $countData[$currDateString][1] + $totalData[$previousDateString][1];
	    			$percentageData[$currDateString][1] = floatval($totalData[$currDateString][1] * 100) / $nbUsers;
	    		}
	    	}
	    	$from = $from->add(new \DateInterval('P1D'));
	    	$previousDateString = $currDateString;
    	}
		
    	// Order the arrays...
    	foreach ($result as &$badgeResult) {
    		ksort($badgeResult['data']['count']);
    		ksort($badgeResult['data']['total']);
    		ksort($badgeResult['data']['percentage']);
    	}
    		
    	return $result;
    }

    /**************************************
     * Requests for keynumbers analytics. *
     **************************************/
    
    public function getTotalSubscribedUsers(AbstractWorkspace $workspace, $filterRoles) {
    	return count($workspace->getAllUsers($filterRoles));
    }
    
    public function getTotalSubscribedUsersToday(AbstractWorkspace $workspace, $filterRoles) {
    	return $this->logRepository->countLogsUsersTodayByAction(
    			$workspace,
    			"workspace-role-subscribe_user",
    			$filterRoles);
    }
    
    public function getNumberConnectionsToday(AbstractWorkspace $workspace, $filterRoles) {
    	return $this->logRepository->countLogsUsersTodayByAction(
    			$workspace,
    			"workspace-enter",
    			$filterRoles);
    }
    
    public function getMeanNumberConnectionsDaily(AbstractWorkspace $workspace, $filterRoles) {
    	$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    	$from = $session->getStartDate();
    	$to = $session->getEndDate();
    	
    	$connectionsByDays = $this->logRepository->countLogsUsersActionByDate($workspace, "workspace-enter");
    	$nbDays = $from->diff($to, true)->format("%a");
    	
    	$total = 0;
    	foreach ($connectionsByDays as $connectionByDay) {
    		$total += $connectionByDay['number'];
    	}
    	
    	return $total / $nbDays;
    }
    
    public function getNumberActiveUsers(AbstractWorkspace $workspace, $nbDays, $filterRoles) {
    	$date = new \DateTime("today midnight");
    	$date->sub(new \DateInterval("P".$nbDays."D"));
    	
    	return $this->logRepository->countActiveUsersSinceDate($workspace, $date);
    }

    public function getHourMostConnection(AbstractWorkspace $workspace, $filterRoles) {
    	$hourlyAudience = $this->getHourlyAudience($workspace)[0];
    	
    	$max = 0;
    	$index = 0;
    	foreach($hourlyAudience as $hour => $audience) {
    		if ($audience > $max) {
    			$max = $audience;
    			$index = $hour;
    		}
    	}
    	return $index;
    }
    
    public function getHourMostActivity(AbstractWorkspace $workspace, $filterRoles) {
    	$hourlyAudience = $this->getHourlyAudience($workspace)[1];
    	
    	$max = 0;
    	$index = 0;
    	foreach($hourlyAudience as $hour => $audience) {
    		if ($audience > $max) {
    			$max = $audience;
    			$index = $hour;
    		}
    	}
    	return $index;
    }
    
    public function getTotalForumPublications(AbstractWorkspace $workspace, $filterRoles) {
    	$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    	if ($session->getForum() != null) {
	    	$forum = $session->getForum();
	    	return $this->messageRepository->countNbMessagesInForum($forum, $filterRoles);
    	} else {
    		return 0;
    	}
    }
    
    public function getForumPublicationsDailyMean(AbstractWorkspace $workspace, $filterRoles) {
    	$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    	if ($session != null && $session->getForum() != null) {
	    	$from = $session->getStartDate();
	    	$to = $session->getEndDate();
	    	
	    	$nbDays = $from->diff($to, true)->format('%a');
	    	
	    	$nbMessages = $this->getTotalForumPublications($workspace, $filterRoles);
	    	return $nbMessages / $nbDays;
    	} else {
    		return 0;
    	}
    }
    
    public function getMostActiveSubjects(AbstractWorkspace $workspace, $nbDays, $filterRoles) {
    	$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    	if ($session != null && $session->getForum() != null) {
    		$since = new \DateTime("today midnight");
    		$since = $since->sub(new \DateInterval('P'.$nbDays.'D'));
	    	$forum = $session->getForum();
		    $managerRole = $this->roleManager->getManagerRole($workspace);
	    	return $this->messageRepository->countNbMessagesInForumGroupBySubjectSince(
	    			$forum,
	    			$since,
	    			$filterRoles);
    	} else {
    		return null;
    	}
    }
    
    public function getMostActiveUsers(AbstractWorkspace $workspace, $filterRoles) {
    	$users = $workspace->getAllUsers($filterRoles);
    	$mostActiveUsers = $this->logRepository->countAllLogsByUsers($workspace, $filterRoles);
    	foreach ($users as $user) {
    		$found = false;
    		foreach($mostActiveUsers as $activeUser) {
    			if ($activeUser['user']->getId() == $user->getId()) {
    				$found = true;
    				break;
    			}
    		}
    		if (!$found) {
    			$notActiveUser = array();
    			$notActiveUser['user'] = $user;
    			$notActiveUser['nbLogs'] = 0;
    			$mostActiveUsers[] = $notActiveUser;
    		}
    	}
    	return $mostActiveUsers;
    }
    
    public function getAnalyticsMoocKeyNumbers(AbstractWorkspace $workspace, User $user) {
    	// Init the roles to filter the stats.
    	$excludeRoles = array();
    	$managerRole = $this->roleManager->getManagerRole($workspace);
    	$excludeRoles[] = $managerRole->getName();
    	$excludeRoles[] = "ROLE_ADMIN";
    	$excludeRoles[] = "ROLE_WS_CREATOR";
    	
   		$nbConnectionsToday = array(
    			"key" => $this->getTranslationKeyForKeynumbers('connections_today'),
    			"value" => $this->getNumberConnectionsToday($workspace, $excludeRoles));
    	
    	$meanConnectionsDaily = array(
    			"key" => $this->getTranslationKeyForKeynumbers('mean_connections_daily'),
    			"value" => $this->getMeanNumberConnectionsDaily($workspace, $excludeRoles));
    	
    	$nbSubscriptionsToday = array(
    			"key" => $this->getTranslationKeyForKeynumbers('subscriptions_today'),
    			"value" => $this->getTotalSubscribedUsersToday($workspace, $excludeRoles));
    	
    	$nbSubscriptions = array(
    			"key" => $this->getTranslationKeyForKeynumbers('subscriptions_total'),
    			"value" => $this->getTotalSubscribedUsers($workspace, $excludeRoles));
    	
    	$nbActiveUsers = array(
    			"key" => $this->getTranslationKeyForKeynumbers('active_users'),
    			"value" => $this->getNumberActiveUsers($workspace, 5, $excludeRoles));
    	
    	$mostConnectedHour = array(
    			"key" => $this->getTranslationKeyForKeynumbers('connection_hour'),
    			"value" => $this->getHourMostConnection($workspace, $excludeRoles));
    	
    	$mostActiveHour = array(
    			"key" => $this->getTranslationKeyForKeynumbers('activity_hour'),
    			"value" => $this->getHourMostActivity($workspace, $excludeRoles));
    	
    	$nbForumPublications = array(
    			"key" => $this->getTranslationKeyForKeynumbers('forum_publications_total'),
    			"value" => $this->getTotalForumPublications($workspace, $excludeRoles));
    	
    	$meanForumPublicationsDaily = array(
    			"key" => $this->getTranslationKeyForKeynumbers('forum_publications_daily_mean'),
    			"value" => $this->getForumPublicationsDailyMean($workspace, $excludeRoles));
        
        return array(
            'workspace' => $workspace,
            'keynumbers' => array(
                $nbConnectionsToday,
                $meanConnectionsDaily,
                $nbSubscriptionsToday,
                $nbSubscriptions,
                $nbActiveUsers,
                $mostConnectedHour,
                $mostActiveHour,
                $nbForumPublications,
                $meanForumPublicationsDaily
            )
        );
    }
    
    private function getTranslationKeyForKeynumbers($id) {
    	return $this->translator->trans('mooc_analytics_keynumbers_'.$id, array(), 'platform');
    }
}
