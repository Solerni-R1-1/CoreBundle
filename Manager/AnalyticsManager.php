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
use Claroline\CoreBundle\Repository\Analytics\AnalyticsMoocStatsRepository;
use Claroline\CoreBundle\Entity\Analytics\AnalyticsBadgeMoocStats;
use Claroline\CoreBundle\Repository\Analytics\AnalyticsBadgeMoocStatsRepository;
use Claroline\CoreBundle\Repository\Analytics\AnalyticsUserMoocStatsRepository;
use Doctrine\ORM\EntityManager;

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
    /** @var AnalyticsMoocStatsRepository */
    private $analyticsMoocStatsRepo;
    /** @var AnalyticsHourlyMoocStatsRepository */
    private $analyticsHourlyMoocStatsRepo;
    /* @var $analyticsUserMoocStatsRepo AnalyticsUserMoocStatsRepository */
    private $analyticsUserMoocStatsRepo;
    /** @var AnalyticsBadgeMoocStatsRepository */
    private $analyticsBadgeMoocStatsRepo;
    /** @var moocSessionRepository */
    private $moocSessionRepository;
    
    private $translator;
    
    private $roleManager;
    
    private $entityManager;

    /**
     * @DI\InjectParams({
     *     "objectManager"      = @DI\Inject("claroline.persistence.object_manager"),
     *     "moocService"        = @DI\Inject("orange.mooc.service"),
     *     "badgeManager"       = @DI\Inject("claroline.manager.badge"),
     *     "translator"         = @DI\Inject("translator"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "entityManager"      = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(
    		ObjectManager $objectManager,
    		MoocService $moocService,
    		BadgeManager $badgeManager,
    		TranslatorInterface $translator,
    		RoleManager $roleManager,
            EntityManager $entityManager)
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
        $this->analyticsMoocStatsRepo =         $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsMoocStats');
        $this->analyticsHourlyMoocStatsRepo =   $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsHourlyMoocStats');
        $this->analyticsUserMoocStatsRepo =     $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsUserMoocStats');
        $this->analyticsBadgeMoocStatsRepo =    $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsBadgeMoocStats');
        $this->moocSessionRepository =          $objectManager->getRepository('ClarolineCoreBundle:Mooc\MoocSession');
        $this->translator       =               $translator;
        $this->roleManager		=               $roleManager;
        $this->entityManager    =               $entityManager;

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
    public function getHourlyAudience(MoocSession $session, $filteredRoles) {
    	$audience = array();
    	$audience[] = array();
    	$audience[] = array();
    	
    	$connectionsByHour = $this->analyticsHourlyMoocStatsRepo->sumHourlyActionsIncluding($session, array("workspace-enter"));
    	$activityByHour = $this->analyticsHourlyMoocStatsRepo->sumHourlyActionsExcluding($session, array("workspace-enter", "workspace-role-subscribe_group", "workspace-role-subscribe_user", "workspace-role-unsubscribe_group", "workspace-role-unsubscribe_user"));
    	
    	for ($i = 0; $i < 24; $i++) {
    		$audience[0][$i] = intval($connectionsByHour["h".$i]);
    		$audience[1][$i] = intval($activityByHour["h".$i]);
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
    public function getSubscriptionsForPeriod(MoocSession $moocSession) {
    	$subscriptions = array();
    	$subscriptions[] = array();
    	$subscriptions[] = array();
    	$subscriptions[] = array();
    	
    	$from 	= new \DateTime($moocSession->getStartInscriptionDate()->format("Y-m-d"));
    	$to 	= new \DateTime($moocSession->getEndInscriptionDate()->format("Y-m-d"));
    	$now 	= new \DateTime("today midnight");
    	
    	if ($now < $to) {
    		$to = $now;
    	}

    	$subscriptionsByDay = $this->analyticsMoocStatsRepo->getSubscriptionsAndConnectionsByDay($moocSession);
    	$orderedSubByDay = array();
    	foreach ($subscriptionsByDay as $subscriptionsForDay) {
    		if (!array_key_exists($subscriptionsForDay["date"]->format("Y-m-d"), $orderedSubByDay)) {
    			$orderedSubByDay[$subscriptionsForDay["date"]->format("Y-m-d")] = array();
    		}
    		$orderedSubByDay[$subscriptionsForDay["date"]->format("Y-m-d")]["nbSubscriptions"] = $subscriptionsForDay["nbSubscriptions"];
    		$orderedSubByDay[$subscriptionsForDay["date"]->format("Y-m-d")]["nbConnections"] = $subscriptionsForDay["nbConnections"];
    	}
    	
    	$totalSubscriptions = 0;
    	$index = 0;
    	while ($from != $to) {
    		$dateCurrent = $from->format("Y-m-d");
    		$subscriptions[0][$index][0] = $dateCurrent;
    		$subscriptions[1][$index][0] = $dateCurrent;
    		$subscriptions[2][$index][0] = $dateCurrent;
    		
    		if (array_key_exists($dateCurrent, $orderedSubByDay)) {
    			$nbSub = intval($orderedSubByDay[$dateCurrent]["nbSubscriptions"]);
    			$nbConn = intval($orderedSubByDay[$dateCurrent]["nbConnections"]);
    		} else {
    			$nbSub = 0;
    			$nbConn = 0;
    		}

    		$totalSubscriptions += $nbSub;
    		$subscriptions[0][$index][1] = $totalSubscriptions;
    		$subscriptions[1][$index][1] = $nbSub;
    		$subscriptions[2][$index][1] = $nbConn;
    		
    		$from->add(new \DateInterval("P1D"));
    		$index++;
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
    public function getPercentageActiveMembers(MoocSession $session, $nbDays = 7, $filteredRoles) {
    	return [$this->getNumberActiveUsers($session, $nbDays, $filteredRoles),
    			$this->getTotalSubscribedUsers($session, $filteredRoles)];
    }
    
    /**
     * 
     * @param AbstractWorkspace $workspace
     */
    public function getForumActivity(MoocSession $session) {
    	$contributions      = array();
    	$contributions[0]   = array();
    	$contributions[1]   = 0;
    	
		$messagesPerDay = $this->analyticsUserMoocStatsRepo->countDailyForumMessagesForSession($session);
		
		$from = new \DateTime($session->getStartDate()->format("Y-m-d"));
		$to = new \DateTime($session->getEndDate()->format("Y-m-d"));
		$now = new \DateTime("today midnight");
		if ($now < $to) {
			$to = $now;
		}
		$oneDay = new \DateInterval("P1D");
		
		while ($from <= $to) {
			$contrib = array();
			$contrib[0] = $from->format("Y-m-d");
			$contrib[1] = 0;
			foreach ($messagesPerDay as $i => $message) {
				if ($message["date"] == $from) {
					$contrib[1] = intval($message["nbPublicationsForum"]);
					break;						
				}
			}
			
			$contributions[0][] = $contrib;
			
			$from->add($oneDay);
		}
    	
    	return $contributions;
    }
    
    public function getForumStats(MoocSession $session, array $userIds, $limit = 0) {
    	return $this->analyticsUserMoocStatsRepo->countForumMessagesForSessionByUsers($session, $userIds, $limit);
    }
    
   public function  getBadgesRate(MoocSession $session, $filteredRoles, $skillBadges, $knowledgeBadges) {
	   	$badgeStats = $this->analyticsBadgeMoocStatsRepo->findBy(array(
	   		"workspace" => $session->getMooc()->getWorkspace(),
	   		"badgeType" => $skillBadges ? "skill" : "knowledge"));
	   	$badgesSuccessRates = $this->analyticsBadgeMoocStatsRepo->countBadgesSuccessRateForSession($session);
	   	$participationRates = array();
	   	$successRates = array();
	   	
	   	$orderedBadgeStats = array();
	   	$badges = array();
	   	foreach ($badgeStats as $badgeStat) {
	   		$badgeId = $badgeStat->getBadge()->getId();
	   		$participationRates[$badgeId] = array();
	   		$participationRates[$badgeId]["badge"] = $badgeStat->getBadge();
	   		$participationRates[$badgeId]["data"] = array();
	   		$participationRates[$badgeId]["data"]["count"] = array();
	   		$participationRates[$badgeId]["data"]["total"] = array();
	   		
	   		$date = $badgeStat->getDate()->format("Y-m-d");
	   		if (!array_key_exists($badgeId, $orderedBadgeStats)) {
	   			$orderedBadgeStats[$badgeId] = array();
	   		}
	   		
	   		if (!array_key_exists($date, $orderedBadgeStats[$badgeId])) {
	   			$orderedBadgeStats[$badgeId][$date] = array();
	   		}
	   		
	   		$orderedBadgeStats[$badgeId][$date] = $badgeStat->getNbParticipations();
	   	}
	   	
	    $oneDay = new \DateInterval("P1D");
	    $from = new \DateTime($session->getStartDate()->format("Y-m-d"));
	    $to = new \DateTime($session->getEndDate()->format("Y-m-d"));
	    $now = new \DateTime("today midnight");
	    if ($now < $to) {
	    	$to = $now;
	    }
	    $to->add($oneDay);
	    
	    foreach ($badgesSuccessRates as $badgeSuccessRates) {
	    	$rateBadge = array();
	    	$rateBadge['success'] = $badgeSuccessRates['totalSuccess'];
	    	$rateBadge['failure'] = $badgeSuccessRates['totalFail'];
	    	$rateBadge['inProgress'] = $badgeSuccessRates['totalParticipations'];
	    	$rateBadge['available'] = $this->getTotalUsersWithGroupsForSession($session)
	    		- ($badgeSuccessRates['totalParticipations']
	    				+ $badgeSuccessRates['totalSuccess']
	    				+ $badgeSuccessRates['totalFail']);
	    	$rateBadge['name'] = $badgeSuccessRates["badge"]->getName();
	    	$rateBadge['id'] = $badgeSuccessRates["badge"]->getId();
	    	$rateBadge['type'] = $badgeSuccessRates["type"];
	    	
	    	if ($rateBadge['type'] == "skill" && $skillBadges
	    		|| $rateBadge['type'] == "knowledge" && $knowledgeBadges) {
	    		$successRates[$badgeSuccessRates["badge"]->getName()] = $rateBadge;
	    	}
	    }
	    
	    $totals = array();
	    while ($from < $to) {
	    	foreach ($participationRates as &$participationBadgeRate) {
	    		$badgeId = $participationBadgeRate["badge"]->getId();
	    		$date = $from->format("Y-m-d");
	    		
	    		$count = 0;
	    		if (array_key_exists($date, $orderedBadgeStats[$badgeId])) {
	    			$count = $orderedBadgeStats[$badgeId][$date]; 
	    		}
	    		
	    		if (!array_key_exists($badgeId, $totals)) {
	    			$totals[$badgeId] = 0; 
	    		}
	    		$totals[$badgeId] = $totals[$badgeId] + $count;
	    		
	    		$participationBadgeRate["data"]["count"][$date] = array($date, $count);
	     		$participationBadgeRate["data"]["total"][$date] = array($date, $totals[$badgeId]);
	     	}
	     	$from->add($oneDay);
	     }
	   	
	    return array("participation" => $participationRates, "success" => $successRates);
    }

    /**************************************
     * Requests for keynumbers analytics. *
     **************************************/
    
    public function getTotalSubscribedUsers(MoocSession $session) {
    	return $this->getTotalUsersWithGroupsForSession( $session );
    }
    
    public function getTotalSubscribedUsersToday(MoocSession $session, $filterRoles) {
    	return $this->logRepository->countLogsUsersTodayByAction(
    			$session->getMooc()->getWorkspace(),
    			"workspace-role-subscribe_user",
    			$filterRoles);
    }
    
    public function getNumberConnectionsToday(MoocSession $session, $filterRoles) {
    	return $this->logRepository->countLogsUsersTodayByAction(
    			$session->getMooc()->getWorkspace(),
    			"workspace-enter",
    			$filterRoles);
    }
    
    public function getMeanNumberConnectionsDaily(MoocSession $session, $filterRoles) {
    	$mean = $this->analyticsMoocStatsRepo->avgConnectionsForSession($session);
    	return $mean != null ? $mean : 0;
    }
    
    public function getNumberActiveUsers(MoocSession $session, $nbDays, $filterRoles) {
    	return $this->analyticsUserMoocStatsRepo->countActiveUsersSince(
    			$session,
    			$nbDays);
    }

    public function getHourMostConnection(MoocSession $session, $filterRoles) {    	
    	$connectionsByHour = $this->analyticsHourlyMoocStatsRepo->sumHourlyActionsIncluding(
    			$session,
    			array("workspace-enter"));
    	  	    	
    	$max = 0;
    	$index = 0;
    	for ($hour = 0; $hour < 24; $hour++) {
    		$audience = $connectionsByHour["h".$hour];
    		if ($audience > $max) {
    			$max = $audience;
    			$index = $hour;
    		}
    	}
    	return $index;
    	return $index;
    }
    
    public function getHourMostActivity(MoocSession $session, $filterRoles) {
    	$activityByHour = $this->analyticsHourlyMoocStatsRepo->sumHourlyActionsExcluding(
    			$session,
    			array("workspace-enter",
    					"workspace-role-subscribe_group",
    					"workspace-role-subscribe_user",
    					"workspace-role-unsubscribe_group",
    					"workspace-role-unsubscribe_user"));
    	
    	$max = 0;
    	$index = 0;
    	for ($hour = 0; $hour < 24; $hour++) {
    		$audience = $activityByHour["h".$hour];
    		if ($audience > $max) {
    			$max = $audience;
    			$index = $hour;
    		}
    	}
    	return $index;
    }
    
    /*
     * Return int number of message forum
     */
    public function getTotalForumPublications( MoocSession $session ) {
        
        // Get forum resource Node Id
        $forumNodeId =  $session->getForum()->getId();
        
        if ( $forumNodeId ) {
            $number = $this->analyticsUserMoocStatsRepo->countTotalForumMessagesForSession( $forumNodeId );
        } else {
             $number = 0;
        }
        
        return $number;
    }
    
    public function getForumPublicationsDailyMean( $session, $totalForumPublications ) {
        
    	$from = new \DateTime($session->getStartDate()->format("Y-m-d"));
    	$to = new \DateTime($session->getEndDate()->format("Y-m-d"));
    	$now = new \DateTime("today midnight");
        
    	if ($now < $to) {
    		$to = $now;
    	}
    	
    	$nbDays = date_diff($from, $to, true)->format("%a") + 1;
    	
    	return $totalForumPublications / $nbDays;
    }
    
    public function getMostActiveSubjects(MoocSession $session, $nbDays, $filterRoles) {
    	if ($session != null && $session->getForum() != null) {
    		$since = new \DateTime("today midnight");
    		$since = $since->sub(new \DateInterval('P'.$nbDays.'D'));
	    	$forum = $session->getForum();
	    	return $this->messageRepository->countNbMessagesInForumGroupBySubjectSince(
	    			$forum,
	    			$since,
	    			$filterRoles);
    	} else {
    		return null;
    	}
    }
    
    public function getMostActiveUsers(MoocSession $moocSession, array $userIds, $limit = 0) {
    	return $this->analyticsUserMoocStatsRepo->getUsersActivity($moocSession, $userIds, $limit);
    }
    
    public function getAnalyticsMoocKeyNumbers(MoocSession $session, User $user) {
    	// Init the roles to filter the stats.
        $excludeRoles = $this->getExcludedRoles($session);
        
        $totalForumPublications = $this->getTotalForumPublications( $session );
    	
   		$nbConnectionsToday = array(
    			"key" => $this->getTranslationKeyForKeynumbers('connections_today'),
    			"value" => $this->getNumberConnectionsToday($session, $excludeRoles));
    	
    	$meanConnectionsDaily = array(
    			"key" => $this->getTranslationKeyForKeynumbers('mean_connections_daily'),
    			"value" => $this->getMeanNumberConnectionsDaily($session, $excludeRoles));
    	
    	$nbSubscriptionsToday = array(
    			"key" => $this->getTranslationKeyForKeynumbers('subscriptions_today'),
    			"value" => $this->getTotalSubscribedUsersToday($session, $excludeRoles));
    	
    	$nbSubscriptions = array(
    			"key" => $this->getTranslationKeyForKeynumbers('subscriptions_total'),
    			"value" => $this->getTotalUsersWithGroupsForSession($session));
    	
    	$nbActiveUsers = array(
    			"key" => $this->getTranslationKeyForKeynumbers('active_users'),
    			"value" => $this->getNumberActiveUsers($session, 7, $excludeRoles));
    	
    	$mostConnectedHour = array(
    			"key" => $this->getTranslationKeyForKeynumbers('connection_hour'),
    			"value" => $this->getHourMostConnection($session, $excludeRoles));
    	
    	$mostActiveHour = array(
    			"key" => $this->getTranslationKeyForKeynumbers('activity_hour'),
    			"value" => $this->getHourMostActivity($session, $excludeRoles));
    	
    	$nbForumPublications = array(
    			"key" => $this->getTranslationKeyForKeynumbers('forum_publications_total'),
    			"value" => $totalForumPublications
        );
    	
    	$meanForumPublicationsDaily = array(
    			"key" => $this->getTranslationKeyForKeynumbers('forum_publications_daily_mean'),
    			"value" => $this->getForumPublicationsDailyMean( $session, $totalForumPublications )
        );
        
        return array(
            'workspace' => $session->getMooc()->getWorkspace(),
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
    
    public function getTotalUsersWithGroupsForSession( $session ) {
        
        $moocSessionId = $session->getId();
                
        $sql = "SELECT COUNT(DISTINCT id) AS subscribed_users
                FROM ( (
                    SELECT DISTINCT u.id AS id 
                    FROM claro_mooc_session ms 
                    INNER JOIN claro_mooc m ON ms.mooc_id = m.id 
                    INNER JOIN claro_workspace w ON m.workspace_id = w.id 
                    INNER JOIN claro_role r ON w.id = r.workspace_id 
                    INNER JOIN claro_user_mooc_session ums ON ms.id = ums.moocsession_id 
                    INNER JOIN claro_user u ON ums.user_id = u.id 
                    where ms.id= $moocSessionId
                    AND u.id NOT IN (
                            SELECT DISTINCT u.id 
                            FROM claro_user u 
                            INNER JOIN claro_user_role ur ON u.id = ur.user_id 
                            INNER JOIN claro_role r ON r.id = ur.role_id 
                            WHERE r.name = 'ROLE_ADMIN' -- or r.name = 'ROLE_WS_CREATOR' 
                            )
                    )
                    UNION (
                    SELECT DISTINCT u.id AS id 
                    FROM claro_mooc_session ms 
                    INNER JOIN claro_mooc m ON m.id = ms.mooc_id 
                    INNER JOIN claro_group_mooc_session gms ON ms.id = gms.moocsession_id 
                    INNER JOIN claro_user_group ug ON gms.group_id = ug.group_id 
                    INNER JOIN claro_user u ON ug.user_id = u.id 
                    INNER JOIN claro_group cg ON ug.group_id = cg.id 
                    WHERE ms.id= $moocSessionId
                    AND u.id NOT IN (
                            SELECT DISTINCT u.id 
                            FROM claro_user u 
                            INNER JOIN claro_user_role ur ON u.id = ur.user_id 
                            INNER JOIN claro_role r ON r.id = ur.role_id 
                            WHERE r.name = 'ROLE_ADMIN' -- or r.name = 'ROLE_WS_CREATOR' 
                            )
                    )
                )as tab_inscrits";
        
        $rows = $this->entityManager->getConnection()->fetchAll($sql);
        
        $result = false;
        
        if ( $rows[0]['subscribed_users'] ) {
            $result = $rows[0]['subscribed_users'];
        }

        return $result;
        
    }

    public function getExcludedRoles( $session ) {
        
        $excludeRoles = array();
        //$managerRole = $this->roleManager->getManagerRole( $session->getMooc()->getWorkspace() );
        //$excludeRoles[] = $managerRole->getName();
        $excludeRoles[] = "ROLE_ADMIN";
        //$excludeRoles[] = "ROLE_WS_CREATOR";
        
        return $excludeRoles;
    }
}
