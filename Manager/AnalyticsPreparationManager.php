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
use Claroline\CoreBundle\Repository\Analytics\AnalyticsLastPreparationRepository;
use Claroline\CoreBundle\Entity\Analytics\AnalyticsMoocStats;
use Claroline\CoreBundle\Entity\Analytics\AnalyticsHourlyMoocStats;
use Claroline\CoreBundle\Entity\Analytics\AnalyticsUserMoocStats;
use Claroline\CoreBundle\Entity\Analytics\AnalyticsBadgeMoocStats;

/**
 * @DI\Service("claroline.manager.analytics_preparation_manager")
 */
class AnalyticsPreparationManager
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
    /** @var AnalyticsLastPreparationRepository */
    private $lastPrepRepo;
    /** @var UserManager */
    private $userManager;
    private $userMoocStatsRepo;
    private $moocStatsRepo;
    private $houlryMoocStatsRepo;
    private $badgeMoocStatsRepo;
    
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *     "moocService"   = @DI\Inject("orange.mooc.service"),
     *     "badgeManager"  = @DI\Inject("claroline.manager.badge"),
     *     "translator"    = @DI\Inject("translator"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"   = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
    		ObjectManager $objectManager,
    		MoocService $moocService,
    		BadgeManager $badgeManager,
    		TranslatorInterface $translator,
    		RoleManager $roleManager,
    		UserManager $userManager) {
        $this->om            	= $objectManager;
        $this->moocService 		= $moocService;
        $this->badgeManager		= $badgeManager;
        $this->userManager		= $userManager;
        $this->resourceRepo  	= $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->resourceTypeRepo	= $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->userRepo      	= $objectManager->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo 	= $objectManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->logRepository 	= $objectManager->getRepository('ClarolineCoreBundle:Log\Log');
        $this->messageRepository= $objectManager->getRepository('ClarolineForumBundle:Message');
        $this->badgeRepository 	= $objectManager->getRepository('ClarolineCoreBundle:Badge\Badge');
        $this->lastPrepRepo 	= $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsLastPreparation');
        $this->moocStatsRepo 	= $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsMoocStats');
        $this->userMoocStatsRepo 	= $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsUserMoocStats');
        $this->hourlyMoocStatsRepo 	= $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsHourlyMoocStats');
        $this->badgeMoocStatsRepo 	= $objectManager->getRepository('ClarolineCoreBundle:Analytics\AnalyticsBadgeMoocStats');
        $this->translator       = $translator;
        $this->roleManager		= $roleManager;

    }
    
    public function prepareConnectionsAndSubscriptionsByDay(MoocSession $moocSession, $excludeRoles = array()) {
    	$workspace = $moocSession->getMooc()->getWorkspace();
    	// Get date start and date end
    	$end = new \DateTime("today midnight");
    	$start = $this->lastPrepRepo->findOneByClassname("AnalyticsMoocStats");
    	if ($start == null) {
    		$start = new \DateTime($moocSession->getStartInscriptionDate()->format("Y-m-d"));
    	}
		
    	if ($end > $moocSession->getEndDate()) {
    		$end = $moocSession->getEndDate();
    	}
    	// Init arrays
    	$actionArray = $this->logRepository->getAllActions();
    	$actions = array();
    	foreach ($actionArray as $action) {
    		$actions[] = $action["action"];
    	}
    	
    	// Get information from database
    	$logs = $this->logRepository->countLogsByDay(
    			$workspace,
    			$start,
    			$end,
    			$excludeRoles);

    	$dateArray = $this->createDateRangeArray($start, $end);
    	
    	$existMoocStats = $this->moocStatsRepo->findByWorkspace($moocSession->getMooc()->getWorkspace());
    	$existHourlyMoocStats = $this->hourlyMoocStatsRepo->findByWorkspace($moocSession->getMooc()->getWorkspace());
    	
    	$hourlyMoocStats = array();
    	foreach ($dateArray as $date) {
    		$dateString = $date->format("Y-m-d");
    		$hourlyMoocStats[$dateString] = array();
    		foreach ($actions as $action) {
    			foreach ($existHourlyMoocStats as $existHourlyMoocStat) {
    				if ($existHourlyMoocStat->getAction() == $action && $existHourlyMoocStat->getDate()->format('Y-m-d') == $dateString) {
    					$hourlyMoocStats[$dateString][$action] = $existHourlyMoocStat;
    					// Reset hours
    					for ($i = 0; $i < 24; $i++) {
    						$hourlyMoocStats[$dateString][$action]->setHourValue($i, 0);
    					}
    					continue 2;    					
    				}
    			}
    			$newHourlyStat = new AnalyticsHourlyMoocStats();
    			$newHourlyStat->setDate($date);
    			$newHourlyStat->setAction($action);
    			$newHourlyStat->setWorkspace($workspace);
    			$hourlyMoocStats[$dateString][$action] = $newHourlyStat;
    		}
    	}
    	
    	$nbDays = count($dateArray);
    	$step = $nbDays / 100;
    	if ($nbDays > 0) {
	    	
	    	$result = 0;
	    	$orderedLogs = $this->arrayToDateMapActionMapNbUsers($logs);
	    	
	    	//print_r($orderedLogs);
	    	for ($i = 0; $i < $nbDays; $i++) {
	    		$date = $dateArray[$i];
	    		$dateString = $date->format("Y-m-d");

	    		$moocConn = null;
	    		foreach ($existMoocStats as $index => $existing) {
	    			if ($existing->getDate() == $date) {
	    				if ($moocConn != null) {
	    					$this->om->remove($existing);
	    					unset($existMoocStats[$index]);
	    					echo "Deleted doublon at date $dateString... \n";
	    				} else {
	    					$moocConn = $existing;
	    				}
	    			}
	    		}
	    		
	    		if ($moocConn == null) {
	    			$moocConn = new AnalyticsMoocStats();
	    			$moocConn->setWorkspace($moocSession->getMooc()->getWorkspace());
	    			$moocConn->setDate($date);
	    		}
	    		
	    		$nbSubscriptions = 0;
	    		$nbConnections = 0;
	    		for ($hour = 0; $hour < 24; $hour++) {
		    		foreach ($actions as $action) {
			    		if (array_key_exists($dateString, $orderedLogs) && array_key_exists($hour, $orderedLogs[$dateString]) && array_key_exists($action, $orderedLogs[$dateString][$hour])) {
			    			$nbUsers = $orderedLogs[$dateString][$hour][$action];
			    		} else {
			    			$nbUsers = array("nbDoers" => 0, "nbReceivers" => 0, "nbGroupReceivers" => 0);
			    		}
			    		
			    		if ($action == "workspace-enter") {
				    		$nbConnections += $nbUsers["nbDoers"];			    		
				    		$result += $nbConnections;
				    		
			    		} else if ($action == "workspace-role-subscribe_user") {
				    		$nbSubscriptions += $nbUsers["nbReceivers"];
			    		} else if ($action == "workspace-role-subscribe_group") {
				    		$nbSubscriptions += $nbUsers["nbGroupReceivers"];
			    		} else if ($action == "workspace-role-unsubscribe_user") {
				    		$nbSubscriptions -= $nbUsers["nbReceivers"];
			    		} else if ($action == "workspace-role-unsubscribe_group") {
				    		$nbSubscriptions -= $nbUsers["nbGroupReceivers"];
			    		}
			    		$hourlyMoocStats[$dateString][$action]->setHourValue($hour, $hourlyMoocStats[$dateString][$action]->getHourValue($hour) + $nbUsers["nbDoers"]);
		    		}
	    		}
	    		
	    		foreach ($hourlyMoocStats as $hourlyMoocStat) {
	    			foreach($hourlyMoocStat as $actionHourly) {
	    				$totalCount = 0;
	    				for ($hour = 0; $hour < 24; $hour++) {
	    					$totalCount += $actionHourly->getHourValue($hour);
	    				}
	    				if ($totalCount > 0) {
	    					$this->om->persist($actionHourly);
	    				} else if ($actionHourly->getId() > 0) {
	    					$this->om->remove($actionHourly);
	    				}
	    			}
	    		}
				$moocConn->setNbConnections($nbConnections);
				$moocConn->setNbSubscriptions($nbSubscriptions);
				
				$this->om->persist($moocConn);
				if ($i != 0) {
					echo ($i + 1) / $step."%                                                                       \r";
				}
	    	}
	    	echo "\n";
	    	$this->om->flush();
    	}
    }
    
    public function prepareUserAnalytics(MoocSession $moocSession, $excludeRoles = array()) {
    	$workspace = $moocSession->getMooc()->getWorkspace();
    	// Get date start and date end
    	$end = new \DateTime("today midnight");
    	$start = $this->lastPrepRepo->findOneByClassname("AnalyticsMoocStats");
    	if ($start == null) {
    		$start = new \DateTime($moocSession->getStartInscriptionDate()->format("Y-m-d"));
    	}
    	
    	if ($end > $moocSession->getEndDate()) {
    		$end = $moocSession->getEndDate();
    	}
    	
    	// Get actions
		$actions = array("workspace-role-subscribe_user",
				"workspace-role-subscribe_group",
				"workspace-role-unsubscribe_user",
				"workspace-role-unsubscribe_group");

		$userIds = $this->userManager->getWorkspaceUserIds($workspace, $excludeRoles);
		
    	$logs = $this->logRepository->getPreparationForUserAnalytics($workspace, $start, $end, $actions, $userIds);
    	if ($moocSession->getForum() != null) {
    		$forumsData = $this->messageRepository->getPreparationForUserAnalytics($moocSession->getForum(), $start, $end, $excludeRoles);
    	} else {
    		$forumsData = array();
    	}
    	
    	$combinedData = array();
    	foreach ($forumsData as $forumData) {
    		$date = $forumData["date"];
    		$user = $forumData["user"];
    		$nbMessages = $forumData["nbMessages"];
    		
    		if (!array_key_exists($date, $combinedData)) {
    			$combinedData[$date] = array();
    		}
    		
    		if (!array_key_exists($user->getId(), $combinedData[$date])) {
    			$combinedData[$date][$user->getId()] = array();
    			$combinedData[$date][$user->getId()]["date"] = new \DateTime($date);
    			$combinedData[$date][$user->getId()]["user"] = $user;
    		}
    		
    		$combinedData[$date][$user->getId()]["forumMessages"] = $nbMessages;
    	}
    	
    	foreach ($logs as $log) {
    		$date = $log["date"]->format("Y-m-d");
    		$user = $log["doer"];
    		$nbActivity = $log["nbActivity"];
    		
    		if (!array_key_exists($date, $combinedData)) {
    			$combinedData[$date] = array();
    		}
    		
    		if (!array_key_exists($user->getId(), $combinedData[$date])) {
    			$combinedData[$date][$user->getId()] = array();
    			$combinedData[$date][$user->getId()]["date"] = new \DateTime($date);
    			$combinedData[$date][$user->getId()]["user"] = $user;
    		}
    		
    		$combinedData[$date][$user->getId()]["nbActivity"] = $nbActivity;
    	}
    	$this->userMoocStatsRepo->cleanTable($workspace);
    	$this->om->flush();
    	
    	foreach ($combinedData as $userData) {
    		foreach ($userData as $data) {
    			$user = $data["user"];
    			$date = $data["date"];
    			$nbActivity = (array_key_exists("nbActivity", $data) ? $data["nbActivity"] : 0);
    			$nbPublicationsForum = (array_key_exists("forumMessages", $data) ? $data["forumMessages"] : 0);
    			
    			//$stat = $this->userMoocStatsRepo->findOneBy(array("workspace" => $workspace, "date" => $date, "user" => $user));
    			//if ($stat == null) {
    				$stat = new AnalyticsUserMoocStats();
    				$stat->setWorkspace($workspace);
    				$stat->setDate($date);
    				$stat->setUser($user);
    			//}
    			
    			$stat->setNbActivity($nbActivity);
    			$stat->setNbPublicationsForum($nbPublicationsForum);
    			
    			$this->om->persist($stat);
    		}
    	}
    	
    	$this->om->flush();
    }
    

    public function prepareBadgeAnalytics(MoocSession $moocSession, $excludeRoles = array()) {
    	// Init
    	$workspace = $moocSession->getMooc()->getWorkspace();
    	$badgeRepo = $this->om->getRepository("ClarolineCoreBundle:Badge\Badge");
    	
    	// Get existing badgeStats
    	$badgeStats = $this->badgeMoocStatsRepo->findByWorkspace($workspace);
    	$badges = $this->badgeRepository->findByWorkspace($workspace);
    	$badgesMap = array();
    	foreach ($badges as $badge) {
    		$badgesMap[$badge->getId()] = $badge;
    	}
    	
    	// Order them in the badgeStatsArray to access them faster
    	$badgeStatsArray = array();
    	foreach ($badgeStats as $badgeStat) {
    		$dateString = $badgeStat->getDate()->format("Y-m-d");
    		if (!array_key_exists($dateString, $badgeStatsArray)) {
    			$badgeStatsArray[$dateString] = array();
    		}
    		$badgeStatsArray[$dateString][$badgeStat->getBadge()->getId()] = $badgeStat;
    		$badgeStat->setNbParticipations(0);
    		$badgeStat->setNbSuccess(0);
    		$badgeStat->setNbFail(0);
    		
    		$this->om->persist($badgeStat);
    	}
    	
    	echo "0%";
    	// Get participations data
    	$skillData = $badgeRepo->getSkillBadgesParticipationRates($moocSession, $excludeRoles);
    	$knowledgeData = $badgeRepo->getKnowledgeBadgesParticipationRates($moocSession, $excludeRoles);
    	$data = array_merge($skillData, $knowledgeData);
    	foreach ($data as $badgeDay) {
    		/*$badge = new Badge();//$badgeDay["badge"];
    		$badge->setId($badgeDay["b_id"]);*/
    		$badge = $badgesMap[$badgeDay["b_id"]];
    		$date = new \DateTime($badgeDay["date"]);
    		$nbParticipations = $badgeDay["nbParticipations"];
    		
    		if (!array_key_exists($badgeDay["date"], $badgeStatsArray)) {
    			$badgeStatsArray[$badgeDay["date"]] = array();
    		}
    		if (!array_key_exists($badge->getId(), $badgeStatsArray[$badgeDay["date"]])) {
    			$badgeStat = new AnalyticsBadgeMoocStats();
    			$badgeStat->setBadge($badge);
    			$badgeStat->setDate($date);
    			$badgeStat->setWorkspace($workspace);
    			$badgeStat->setBadgeType($badgeDay["type"]);
    			$badgeStatsArray[$badgeDay["date"]][$badge->getId()] = $badgeStat;
    		} else {
    			$badgeStat = $badgeStatsArray[$badgeDay["date"]][$badge->getId()];
    		}
    		
    		$badgeStat->setNbParticipations($nbParticipations);
    		
    		$this->om->persist($badgeStat);
    	}
    	
    	echo "\r30%";
    	// Get success data
    	$badgesSuccess = $badgeRepo->getBadgesSuccess($moocSession, $excludeRoles);
    	foreach ($badgesSuccess as $badgeSuccess) {
    		$date = $badgeSuccess["date"];
    		$badge = $badgesMap[$badgeSuccess["b_id"]];
    		$nbSuccess = $badgeSuccess["nbSuccess"];
    		
    		if (!array_key_exists($date, $badgeStatsArray)) {
    			$badgeStatsArray[$date] = array();
    		}
    		
    		if (!array_key_exists($badge->getId(), $badgeStatsArray[$date])) {
	    		$badgeStat = new AnalyticsBadgeMoocStats();
	    		$badgeStat->setBadge($badge);
	    		$badgeStat->setDate(new \DateTime($date));
	    		$badgeStat->setNbParticipations(0);
	    		$badgeStat->setWorkspace($workspace);
    			$badgeStat->setBadgeType($badgeSuccess["type"]);
    		} else {
    			$badgeStat = $badgeStatsArray[$date][$badge->getId()];
    		}
    		$badgeStat->setNbSuccess($nbSuccess);
    		$this->om->persist($badgeStat);
    	}
    	echo "\r60%";
    	// Get failure data
    	$badgesFailures = $badgeRepo->getSkillBadgesFailures($moocSession, $excludeRoles);
    	// TODO : Add knowledge badge failures by day. Not necessary now, failures on quizzes are TotalParticpants - TotalSuccess...
    	foreach ($badgesFailures as $badgeFailures) {
    		$date = $badgeFailures["date"];
    		$badge = $badgesMap[$badgeFailures["b_id"]];
    		$nbFailures = $badgeFailures["nbFailures"];
    		 
    		if (!array_key_exists($date, $badgeStatsArray)) {
    			$badgeStatsArray[$date] = array();
    		}
    		 
    		if (!array_key_exists($badge->getId(), $badgeStatsArray[$date])) {
    			$badgeStat = new AnalyticsBadgeMoocStats();
    			$badgeStat->setBadge($badge);
    			$badgeStat->setDate(new \DateTime($date));
    			$badgeStat->setNbParticipations(0);
    			$badgeStat->setWorkspace($workspace);
    			$badgeStat->setBadgeType($badgeFailures["type"]);
    		} else {
    			$badgeStat = $badgeStatsArray[$date][$badge->getId()];
    		}
    		$badgeStat->setNbFail($nbFailures);
    		$this->om->persist($badgeStat);
    	}
    	echo "\r90%";
    	$this->om->flush();
    	echo "\r100%\n";
    }
    
    private function createDateRangeArray($from, $to) {
    	// takes two dates and creates an
    	// inclusive array of the dates between the from and to dates.
    
    	$result=array();
        
    	if ($to >= $from) {
    		array_push($result, new \DateTime($from->format("Y-m-d"))); // first entry
    		
    		while ($from < $to) {
    			$from->add(new \DateInterval("P1D"));
    			array_push($result, new \DateTime($from->format("Y-m-d")));
    		}
    	}
    	return $result;
    }
    
    private function arrayToDateMapActionMapNbUsers($array) {
    	$result = [];
    	foreach ($array as $elem) {
    		$dateString = $elem['shortDate']->format("Y-m-d");
    		$hour = intval($elem['hour']);
    		if (!isset($result[$dateString])) {
    			$result[$dateString] = array();
    		}
    		if (!isset($result[$dateString][$hour])) {
    			$result[$dateString][$hour] = array();
    		}
    		if (!isset($result[$dateString][$hour][$elem['action']])) {
    			$result[$dateString][$hour][$elem['action']] = array();
    			$result[$dateString][$hour][$elem['action']]["nbDoers"] = 0;
    			$result[$dateString][$hour][$elem['action']]["nbReceivers"] = 0;
    			$result[$dateString][$hour][$elem['action']]["nbGroupReceivers"] = 0;
    		}
    		$result[$dateString][$hour][$elem['action']]["nbDoers"] += $elem['nbDoers'];
    		$result[$dateString][$hour][$elem['action']]["nbReceivers"] += $elem['nbReceivers'];
    		$result[$dateString][$hour][$elem['action']]["nbGroupReceivers"] += $elem['nbGroupReceivers'];
    	}
    	return $result;
    }
}