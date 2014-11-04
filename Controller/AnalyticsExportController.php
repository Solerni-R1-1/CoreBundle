<?php

/*
 * To change this license header, choose License Headers in Project Properties. To change this template file, choose Tools | Templates and open the template in the editor.
 */
namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Log\Log;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Event\Log\LogChapterReadEvent;
use Claroline\CoreBundle\Manager\BadgeManager;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Symfony\Component\HttpFoundation\Response;
use UJM\ExoBundle\Entity\Exercise;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Claroline\ForumBundle\Repository\MessageRepository;
use Claroline\CoreBundle\Controller\Mooc\MoocService;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\Badge\BadgeRepository;
use UJM\ExoBundle\Repository\ExerciseRepository;


/**
 * Description of AnalyticsExportController
 *
 * @author Gregg CESARINE <gcesarine@sii.fr>
 *
 * @copyright 2014 @ sii.fr for Orange
 *
 */
class AnalyticsExportController extends Controller {
	/** @var BadgeManager */
	private $badgeManager;

	/** @var LogRepository */
	private $logRepository;

	/** @var MessageRepository */
	private $messageRepository;
	
	/** @var UserRepository */
	private $userRepository;

	/* @var $exoRepository ExerciseRepository */
	private $exoRepository;
	
	/** @var BadgeRepository */
	private $badgeRepository;
	
	/** @var MoocService */
	private $moocService;
	
	/** @var AnalyticsManager */
	private $analyticsManager;
	
	/** @var Translator */
	private $translator;
	
	/** @var RoleManager */
	private $roleManager;

	/**
	 * @DI\InjectParams({
	 *     "badgeManager"            = @DI\Inject("claroline.manager.badge"),
	 *     "moocService"			 = @DI\Inject("orange.mooc.service"),
	 *     "container"				 = @DI\Inject("service_container"),
	 *     "analyticsManager"		 = @DI\Inject("claroline.manager.analytics_manager"),
	 *     "roleManager"			 = @DI\Inject("claroline.manager.role_manager")
	 *     })
	 */
	public function _construct(
			$container,
			BadgeManager $badgeManager,
			MoocService $moocService, 
			AnalyticsManager $analyticsManager,
			RoleManager $roleManager) {
		$this->setContainer($container);
		
		$this->badgeManager = $badgeManager;
		$this->moocService = $moocService;
		$this->analyticsManager = $analyticsManager;
		$this->roleManager = $roleManager;
		
		$this->logRepository = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Log\Log");
		$this->messageRepository = $this->getDoctrine()->getRepository("ClarolineForumBundle:Message");
		$this->userRepository = $this->getDoctrine()->getRepository("ClarolineCoreBundle:User");
		$this->badgeRepository = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Badge\Badge");
		$this->exoRepository = $this->getDoctrine()->getRepository("UJMExoBundle:Exercise");
		
		
		$this->translator = $this->get('translator');
		
	}
	/**
	 * @EXT\Route(
	 *     "{workspace}/badges/skill",
	 *     name="solerni_export_badges_skill_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportSkillBadgesStatsAction(AbstractWorkspace $workspace) {
		if ($workspace->isMooc()) {
	    	// Init the roles to filter the stats.
	    	$excludeRoles = array();
	    	$managerRole = $this->roleManager->getManagerRole($workspace);
	    	$excludeRoles[] = $managerRole->getName();
	    	$excludeRoles[] = "ROLE_ADMIN";
	    	$excludeRoles[] = "ROLE_WS_CREATOR";
	    	
			$badgesIndex = array();
			$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
			$workspaceUsers = $currentSession->getAllUsers($excludeRoles);

			$headerCSV = array();
			$headerCSV[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$headerCSV[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$headerCSV[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
			$indexBadges = 4;
			$rowsCSV = array();
			
			$usersBadges = array();

			// Get all badges for all users and sort them
			foreach ($workspaceUsers as $user) {
				$usersBadges[$user->getId()] = array();
				$usersBadges[$user->getId()]['user'] = $user;
				$userBadges = array();
				$badges = $this->badgeManager->getAllBadgesForWorkspace($user, $workspace, true, false);
				foreach($badges as $badge) {
					$userBadges[$badge['badge']->getId()] = $badge;
				}
				ksort($userBadges);
				$usersBadges[$user->getId()]['badges'] = $userBadges;
			}

			foreach($usersBadges as $userBadges) {
				$indexBadge = 4;
				$nbBadges = count($userBadges['badges']);
				foreach($userBadges['badges'] as $userBadge) {
					$headerCSV[$indexBadge] = $userBadge['badge']->getName();
					$headerCSV[$indexBadge + $nbBadges] = $userBadge['badge']->getName()." obtenu ?";
					$indexBadge++;
				}
				break;
			}
			ksort($headerCSV);
			
			// For all user associated to his badges
			foreach ($usersBadges as $userBadges) {
				/* @var $user User */
				$user = $userBadges['user'];
				$badges = $userBadges['badges'];
				$nbBadges = count($badges);
				$rowCSV = array();
				$rowCSV[0] = $user->getLastName();
				$rowCSV[1] = $user->getFirstName();
				$rowCSV[2] = $user->getUsername();
				$rowCSV[3] = $user->getMail();
	
				$nbOwnedBadges = 0;
				$totalNotes = 0;
				$nbNotes = 0;
	
				$indexBadge = 4;
				foreach ($badges as $badge) {
					/* @var $badgeEntity Badge */
					$badgeEntity = $badge['badge'];
					$badgeName = $badgeEntity->getName();
						
					/* @var $drop Drop */
					$drop = $badge['resource']['resource']['drop'];
	
					if ($badge['resource']['status'] == Badge::RES_STATUS_SUCCEED
							|| $badge['resource']['status'] == Badge::RES_STATUS_FAILED) {
						$rowCSV[$indexBadge] = $drop->getCalculatedGrade();
					} else {
						$rowCSV[$indexBadge] = "";
					}
					

					if ($badge['status'] == Badge::BADGE_STATUS_OWNED) {
						$rowCSV[$nbBadges + $indexBadge] = "oui";
					} else {
						$rowCSV[$nbBadges + $indexBadge] = "non";
					}
					$indexBadge++;
				}				 
				ksort($rowCSV);
				$rowsCSV[] = $rowCSV;
				 
			}

			array_unshift($rowsCSV, $headerCSV);
			
			$content = $this->createCSVFromArray($rowsCSV);
	
			return new Response($content, 200, array(
					'Content-Type' => 'application/force-download',
					'Content-Disposition' => 'attachment; filename="export.csv"'
			));
		} else {
			throw $this->createNotFoundException('Ce workspace ne contient pas de mooc');
		}
	}
	
	/**
	 * @EXT\Route(
	 *     "{workspace}/badges/knowledge",
	 *     name="solerni_export_badges_knowledge_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportKnowledgeBadgesStatsAction(AbstractWorkspace $workspace) {
		if ($workspace->isMooc()) {
	    	// Init the roles to filter the stats.
	    	$excludeRoles = array();
	    	$managerRole = $this->roleManager->getManagerRole($workspace);
	    	$excludeRoles[] = $managerRole->getName();
	    	$excludeRoles[] = "ROLE_ADMIN";
	    	$excludeRoles[] = "ROLE_WS_CREATOR";
	    	
			$exerciseRepository = $this->getDoctrine()->getRepository("UJMExoBundle:Exercise");
			$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
			//$workspaceUsers = $currentSession->getAllUsers($excludeRoles);
				

			$data = $this->badgeRepository->getKnowledgeBadgesStats($workspace, $excludeRoles);
	
			$headerCSV = array();
			$headerCSV[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$headerCSV[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$headerCSV[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
			//$rowsCSV = array();
			$badgeMaxTries = array();
			$usersBadges = array();
			
			$orderedData = array();
			$maxTries = array();
			$allBadges = array();
			
			// Prepare the data
			foreach ($data as $val) {
				$userId 				= $val['user_id'];
				$userLastname 			= $val['user_lastname'];
				$userFirstname 			= $val['user_firstname'];
				$userMail 				= $val['user_mail'];
				$userUsername			= $val['user_username'];
				$paperId				= $val['paper_id'];
				$paperNum				= $val['paper_num'];
				$badgeId				= $val['badge_id'];
				$badgeName				= $val['badge_name'];
				$mark					= $val['mark'];
				$paperOrdreQuestions 	= $val['paper_ordre_question'];
				
				if (!array_key_exists($userId, $orderedData)) {
					$orderedData[$userId] = array();
					$orderedData[$userId]['user_lastname'] = $userLastname;
					$orderedData[$userId]['user_firstname'] = $userFirstname;
					$orderedData[$userId]['user_username'] = $userUsername;
					$orderedData[$userId]['user_mail'] = $userMail;
					$orderedData[$userId]['badges'] = array();
					
				}
				$badges = &$orderedData[$userId]['badges'];
				if (!array_key_exists($badgeId, $badges)) {
					$badges[$badgeId] = array();
					$badges[$badgeId]['marks'] = array();
					$badges[$badgeId]['name'] = $badgeName;
				}
				
				$marks = &$orderedData[$userId]['badges'][$badgeId]['marks'];
				$maxMark = $this->exoRepository->getMaximalMarkForPaperQCMOrdreQuestion($paperOrdreQuestions);
				$marks[$paperNum] = ($mark / $maxMark) * 20;
				
				if (!array_key_exists($badgeId, $maxTries)) {
					$maxTries[$badgeId] = 0;
				}
				if ($maxTries[$badgeId] < count($marks)) {
					$maxTries[$badgeId] = count($marks);
				}
				
				if (!array_key_exists($badgeId, $allBadges)) {
					$allBadges[$badgeId] = $badgeName;
				}
			}
			
			// Sort everything
			ksort($allBadges);
			foreach ($orderedData as &$d) {
				$badges = &$d['badges']; 
				ksort($badges);
 				foreach ($badges as &$badge) {
 					$marks = &$badge['marks'];
 					ksort($marks);
 				}
			}
			
			// Complete headers
			foreach ($allBadges as $id => $name) {
				for ($i = 1; $i < $maxTries[$id] + 1; $i++) {
					$headerCSV[] = $name.", Essai ".$i;
				}
			}
			
			
			$rowsCSV = array();
			// Concatenate all the data
			foreach ($orderedData as &$d) {
				$rowCSV = array();
				$rowCSV[] = $d['user_lastname']; 
				$rowCSV[] = $d['user_firstname']; 
				$rowCSV[] = $d['user_username'];
				$rowCSV[] = $d['user_mail'];
				foreach ($allBadges as $id => $name) {
					if (array_key_exists($id, $d['badges'])) {
						for ($i = 1; $i < $maxTries[$id] + 1; $i++) {
							if (array_key_exists($i, $d['badges'][$id]['marks'])) {
								$rowCSV[] = $d['badges'][$id]['marks'][$i];
							} else {
								$rowCSV[] = "";
							}
						}
					} else {
						for ($i = 0; $i < $maxTries[$id]; $i++) {
							$rowCSV[] = "";
						}
					}
				}
				$rowsCSV[] = $rowCSV;
			}

			array_unshift($rowsCSV, $headerCSV);
			$content = $this->createCSVFromArray($rowsCSV);
	
			return new Response($content, 200, array(
					'Content-Type' => 'application/force-download',
					'Content-Disposition' => 'attachment; filename="export.csv"'
			));
		} else {
			throw $this->createNotFoundException('Ce workspace ne contient pas de mooc');
		}
	}
	
	/**
	 * @EXT\Route(
	 *     "{workspace}/badges/participation/knowledge",
	 *     name="solerni_export_badges_knowledge_participation_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportKnowledgeBadgesParticipationStatsAction(AbstractWorkspace $workspace) {
		return $this->exportBadgesParticipationStatsAction($workspace, false, true);
	}
	
	/**
	 * @EXT\Route(
	 *     "{workspace}/badges/participation/skill",
	 *     name="solerni_export_badges_skill_participation_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportSkillBadgesParticipationStatsAction(AbstractWorkspace $workspace) {
		return $this->exportBadgesParticipationStatsAction($workspace, true, false);	
	}
	
	public function exportBadgesParticipationStatsAction(AbstractWorkspace $workspace, $skillBadges, $knowledgeBadges) {
		if ($workspace->isMooc()) {
	    	// Init the roles to filter the stats.
	    	$excludeRoles = array();
	    	$managerRole = $this->roleManager->getManagerRole($workspace);
	    	$excludeRoles[] = $managerRole->getName();
	    	$excludeRoles[] = "ROLE_ADMIN";
	    	$excludeRoles[] = "ROLE_WS_CREATOR";
			
			$badgesIndex = array();
			$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
			$workspaceUsers = $currentSession->getAllUsers($excludeRoles);

			$headerCSV = array();
			$headerCSV[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$headerCSV[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$headerCSV[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
			$indexBadges = 4;
			$rowsCSV = array();
			
			$usersBadges = array();

			// Get all badges for all users and sort them
			foreach ($workspaceUsers as $user) {
				$usersBadges[$user->getId()] = array();
				$usersBadges[$user->getId()]['user'] = $user;
				$userBadges = array();
				$badges = $this->badgeManager->getAllBadgesForWorkspace($user, $workspace, $skillBadges, $knowledgeBadges);
				foreach($badges as $badge) {
					$userBadges[$badge['badge']->getId()] = $badge;
				}
				ksort($userBadges);
				$usersBadges[$user->getId()]['badges'] = $userBadges;
			}
			

			foreach($usersBadges as $userBadges) {
				$indexBadge = 4;
				$nbBadges = count($userBadges['badges']);
				foreach($userBadges['badges'] as $userBadge) {
					$headerCSV[$indexBadge] = $userBadge['badge']->getName();
					$indexBadge++;
				}
				break;
			}
			
			// For all user associated to his badges
			foreach ($usersBadges as $userBadges) {
				/* @var $user User */
				$user = $userBadges['user'];
				$badges = $userBadges['badges'];
				$nbBadges = count($badges);
				$rowCSV = array();
				$rowCSV[0] = $user->getLastName();
				$rowCSV[1] = $user->getFirstName();
				$rowCSV[2] = $user->getUsername();
				$rowCSV[3] = $user->getMail();
	
				$nbOwnedBadges = 0;
				$totalNotes = 0;
				$nbNotes = 0;
	
				$indexBadge = 4;
				foreach ($badges as $badge) {
					/* @var $badgeEntity Badge */
					$badgeEntity = $badge['badge'];
					$badgeName = $badgeEntity->getName();
					
					/* @var $drop Drop */
					if (isset($badge['resource']['resource']['drop'])) {
						$drop = $badge['resource']['resource']['drop'];
					} else {
						$drop = null;
					}
					if (isset($badge['resource']['resource']['firstAttempt'])) {
						$firstAttempt = $badge['resource']['resource']['firstAttempt'];
					} else {
						$firstAttempt = null;
					}
	
					if ($drop != null || $firstAttempt != null) {
						$rowCSV[$indexBadge] = "oui";
					} else {
						$rowCSV[$indexBadge] = "non";
					}
					$indexBadge++;
				}				 
				ksort($rowCSV);
				$rowsCSV[] = $rowCSV;
				 
			}

			array_unshift($rowsCSV, $headerCSV);
			
			$content = $this->createCSVFromArray($rowsCSV);
	
			return new Response($content, 200, array(
					'Content-Type' => 'application/force-download',
					'Content-Disposition' => 'attachment; filename="export.csv"'
			));
		} else {
			throw $this->createNotFoundException('Ce workspace ne contient pas de mooc');
		}
	}
	
	/**
	 * @EXT\Route(
	 *     "{workspace}/users/general",
	 *     name="solerni_export_users_general_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportUsersGeneralStatsAction(AbstractWorkspace $workspace) {
    	// Init the roles to filter the stats.
    	$excludeRoles = array();
    	$managerRole = $this->roleManager->getManagerRole($workspace);
    	$excludeRoles[] = $managerRole->getName();
    	$excludeRoles[] = "ROLE_ADMIN";
    	$excludeRoles[] = "ROLE_WS_CREATOR";
    	
		$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
		$from = $currentSession->getStartInscriptionDate();
		$to = $currentSession->getEndInscriptionDate();
				
		$headerCSV = array();

		$headerCSV[0] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$headerCSV[1] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$headerCSV[2] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$headerCSV[4] = "Compte validé ?";
		
		$rowsCSV = $this->userRepository->getAllUsersForExport($currentSession, $excludeRoles);
		
		array_unshift($rowsCSV, $headerCSV);
		
		$content = $this->createCSVFromArray($rowsCSV);
		
		return new Response($content, 200, array(
				'Content-Type' => 'application/force-download',
				'Content-Disposition' => 'attachment; filename="export.csv"'
		));
	}
	
	/**
	 * @EXT\Route(
	 *     "{workspace}/subscriptions",
	 *     name="solerni_export_subscriptions_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportSubscriptionsStatsAction(AbstractWorkspace $workspace) {
    	// Init the roles to filter the stats.
    	$excludeRoles = array();
    	$managerRole = $this->roleManager->getManagerRole($workspace);
    	$excludeRoles[] = $managerRole->getName();
    	$excludeRoles[] = "ROLE_ADMIN";
    	$excludeRoles[] = "ROLE_WS_CREATOR";
    	
		$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
		$from = $currentSession->getStartInscriptionDate();
		$to = $currentSession->getEndInscriptionDate();
		
		$now = new \DateTime();
		if ($now < $to) {
			$to = $now;
		}
		
		$rowsCSV = array();
		
		$headerCSV = array();

		$headerCSV[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$headerCSV[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$headerCSV[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$headerCSV[4] = "Subscription date";
		$headerCSV[5] = "Subscription time";
		$rowsCSV[] = $headerCSV;
		
		// Get information from database
		$logs = $this->logRepository->findAllBetween($workspace, $from, $to, 
    			array(
    				"workspace-role-subscribe_user",	
    				"workspace-role-subscribe_group"),
    			$excludeRoles);
		$users = array();
		
		// Extract data
		foreach ($logs as $i => $log) {
			/* @var $log Log */
			if ($log->getReceiverGroup() != null) {
				foreach ($log->getReceiverGroup()->getUsers() as $user) {
					if (!in_array($user->getId(), $users)) {
						$rowCSV = array();
						$rowCSV[0] = $user->getLastName();
						$rowCSV[1] = $user->getFirstName();
						$rowCSV[2] = $user->getUsername();
						$rowCSV[3] = $user->getMail();
						$rowCSV[4] = $log->getDateLog()->format("d/m/Y");
						$rowCSV[5] = $log->getDateLog()->format("H:i:s");
							
						$rowsCSV[] = $rowCSV;
						$users[] = $user->getId();
					}
				}
			} else {
				$user = $log->getReceiver();

				if (!in_array($user->getId(), $users)) {
					$rowCSV = array();
					$rowCSV[0] = $user->getLastName();
					$rowCSV[1] = $user->getFirstName();
					$rowCSV[2] = $user->getUsername();
					$rowCSV[3] = $user->getMail();
					$rowCSV[4] = $log->getDateLog()->format("d/m/Y");
					$rowCSV[5] = $log->getDateLog()->format("H:i:s");
					
					$rowsCSV[] = $rowCSV;
					$users[] = $user->getId();
				}
			}
			
		}

		$content = $this->createCSVFromArray($rowsCSV);
		
		return new Response($content, 200, array(
				'Content-Type' => 'application/force-download',
				'Content-Disposition' => 'attachment; filename="export.csv"'
		));
	}

	/**
	 * @EXT\Route(
	 *     "{workspace}/connection/{nbDays}",
	 *     name="solerni_export_active_users_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportConnectionStatsAction(AbstractWorkspace $workspace, $nbDays) {
		if ($workspace->isMooc()) {
	    	// Init the roles to filter the stats.
	    	$excludeRoles = array();
	    	$managerRole = $this->roleManager->getManagerRole($workspace);
	    	$excludeRoles[] = $managerRole->getName();
	    	$excludeRoles[] = "ROLE_ADMIN";
	    	$excludeRoles[] = "ROLE_WS_CREATOR";

	    	$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
			$badgesIndex = array();
			//$workspaceUsers = $currentSession->getAllUsers($excludeRoles);
			$mooc = $workspace->getMooc();
	
			$headerCSV = array();
			$headerCSV[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$headerCSV[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$headerCSV[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
			$headerCSV[4] = "Subscription date";
			$headerCSV[5] = "Last connection date";
			
			$rowsCSV = $this->logRepository->getLastConnectionAndSubscriptionForWorkspace($workspace, $excludeRoles);
			
			array_unshift($rowsCSV, $headerCSV);

			$content = $this->createCSVFromArray($rowsCSV);
	
			return new Response($content, 200, array(
					'Content-Type' => 'application/force-download',
					'Content-Disposition' => 'attachment; filename="export.csv"'
			));
		} else {
			throw $this->createNotFoundException('Ce workspace ne contient pas de mooc');
		}
	}
	

	/**
	 * @EXT\Route(
	 *     "{workspace}/forum/publications",
	 *     name="solerni_export_forum_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportForumStatsAction(AbstractWorkspace $workspace) {    	
		$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
		
		$headerCSV = array();
		$header = array();

		$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$header[4] = $this->translator->trans('mooc_analytics_publisher_nb_pub', array(), 'platform');
		
		$headerCSV[] = $header;
		 

		$data = $this->analyticsManager->getForumStats($currentSession);
		
		$rowsCSV = array_merge($headerCSV, $data);
		$content = $this->createCSVFromArray($rowsCSV);
		
		return new Response($content, 200, array(
				'Content-Type' => 'application/force-download',
				'Content-Disposition' => 'attachment; filename="export.csv"'
		));
	}

	/**
	 * @EXT\Route(
	 *     "{workspace}/users/activity",
	 *     name="solerni_export_users_activity"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportUsersActivityAction(AbstractWorkspace $workspace) {
		// Init the roles to filter the stats.
		$excludeRoles = array();
		$managerRole = $this->roleManager->getManagerRole($workspace);
		$excludeRoles[] = $managerRole->getName();
		$excludeRoles[] = "ROLE_ADMIN";
		$excludeRoles[] = "ROLE_WS_CREATOR";
		 
		$headerCSV = array();
		$header = array();
	
		$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$header[4] = $this->translator->trans('mooc_analytics_users_nb_logs', array(), 'platform');
	
		$headerCSV[] = $header;

		$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
		$data = $this->analyticsManager->getMostActiveUsers($currentSession);
// 		$data = array();
// 		foreach ($usersActivity as $userActivity) {
// 			/* @var $user User */
// 			$user = $userActivity['user'];
// 			$row = array();
// 			$row[] = $user->getLastName();
// 			$row[] = $user->getFirstName();
// 			$row[] = $user->getUsername();
// 			$row[] = $user->getMail();
// 			$row[] = $userActivity['nbLogs'];
				
// 			$data[] = $row;
// 		}
	
		$rowsCSV = array_merge($headerCSV, $data);
		$content = $this->createCSVFromArray($rowsCSV);
	
		return new Response($content, 200, array(
				'Content-Type' => 'application/force-download',
				'Content-Disposition' => 'attachment; filename="export.csv"'
		));
	}

	/**
	 * @EXT\Route(
	 *     "{workspace}/users/general",
	 *     name="solerni_export_users_general"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportUsersGeneralAction(AbstractWorkspace $workspace) {
		// Init the roles to filter the stats.
		$excludeRoles = array();
		$managerRole = $this->roleManager->getManagerRole($workspace);
		$excludeRoles[] = $managerRole->getName();
		$excludeRoles[] = "ROLE_ADMIN";
		$excludeRoles[] = "ROLE_WS_CREATOR";
		 
		$headerCSV = array();
		$header = array();
	
		$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$header[4] = $this->translator->trans('mooc_analytics_users_nb_logs', array(), 'platform');
	
		$headerCSV[] = $header;
	
		$usersActivity = $this->analyticsManager->getMostActiveUsers($workspace, $excludeRoles);
		$data = array();
		foreach ($usersActivity as $userActivity) {
			/* @var $user User */
			$user = $userActivity['user'];
			$row = array();
			$row[] = $user->getLastName();
			$row[] = $user->getFirstName();
			$row[] = $user->getUsername();
			$row[] = $user->getMail();
			$row[] = $userActivity['nbLogs'];
				
			$data[] = $row;
		}
	
		$rowsCSV = array_merge($headerCSV, $data);
		$content = $this->createCSVFromArray($rowsCSV);
	
		return new Response($content, 200, array(
				'Content-Type' => 'application/force-download',
				'Content-Disposition' => 'attachment; filename="export.csv"'
		));
	}
	
	private function createCSVFromArray(array $data) {
		$handle = fopen('php://memory', 'r+');
		foreach ($data as $rowCSV) {
			fputcsv($handle, $rowCSV, ';');
		}
		
		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);
		
		return mb_convert_encoding($content, 'UTF-16LE', 'UTF-8');
	}
}
