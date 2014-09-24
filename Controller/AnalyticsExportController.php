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
	
	/** @var MoocService */
	private $moocService;
	
	/** @var AnalyticsManager */
	private $analyticsManager;
	
	/** @var Translator */
	private $translator;

	/**
	 * @DI\InjectParams({
	 *     "badgeManager"            = @DI\Inject("claroline.manager.badge"),
	 *     "moocService"			 = @DI\Inject("orange.mooc.service"),
	 *     "container"				 = @DI\Inject("service_container"),
	 *     "analyticsManager"		 = @DI\Inject("claroline.manager.analytics_manager")
	 *     })
	 */
	public function _construct(
			$container,
			BadgeManager $badgeManager,
			MoocService $moocService, 
			AnalyticsManager $analyticsManager) {
		$this->setContainer($container);
		
		$this->badgeManager = $badgeManager;
		$this->moocService = $moocService;
		$this->analyticsManager = $analyticsManager;
		
		$this->logRepository = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Log\Log");
		$this->messageRepository = $this->getDoctrine()->getRepository("ClarolineForumBundle:Message");
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
			$badgesIndex = array();
			$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
			$workspaceUsers = $currentSession->getUsers();

			$headerCSV = array();
			$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
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
			$exerciseRepository = $this->getDoctrine()->getRepository("UJMExoBundle:Exercise");
			$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
			$workspaceUsers = $currentSession->getUsers();
				
	
	
			$headerCSV = array();
			$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
			$rowsCSV = array();
			$badgeMaxTries = array();
			$usersBadges = array();
			foreach ($workspaceUsers as $user) {
				$badges = $this->badgeManager->getAllBadgesForWorkspace($user, $workspace, false, true);
				$usersBadges[$user->getId()] = array();
				$usersBadges[$user->getId()]['user'] = $user;
				$userBadges = array();
				foreach($badges as $badge) {
					$badgeId = $badge['badge']->getId();
					$nbMarks = sizeof($badge['resource']['resource']['marks']);
					if (!isset($badgeMaxTries[$badgeId]) || $badgeMaxTries[$badgeId] < $nbMarks) {
						$badgeMaxTries[$badgeId] = $nbMarks;
					}
					$userBadges[$badgeId] = $badge;
				}
				ksort($userBadges);
				$usersBadges[$user->getId()]['badges'] = $userBadges;
			}
			
			$totalTries = array_sum($badgeMaxTries);
			
			foreach($usersBadges as $userBadges) {
				$indexBadge = 4;
				foreach($userBadges['badges'] as $userBadge) {
					$index = 1;
					for($i = 0; $i < $badgeMaxTries[$userBadge['badge']->getId()]; $i++) {
						$headerCSV[] = $userBadge['badge']->getName()." - Essai ".$index;
						$index++;
					}
					$headerCSV[$indexBadge + $totalTries] = $userBadge['badge']->getName()." obtenu ?";
					$indexBadge++;
				}
				break;
			}
			
			
			
			foreach ($usersBadges as $userBadges) {
				/* @var $user User */
				$user = $userBadges['user'];
				$badges = $userBadges['badges'];
				$rowCSV = array();
	
				$rowCSV[0] = $user->getLastName();
				$rowCSV[1] = $user->getFirstName();
				$rowCSV[2] = $user->getUsername();
				$rowCSV[3] = $user->getMail();
	
				$nbOwnedBadges = 0;
				$totalNotes = 0;
				$nbNotes = 0;
				$index = 4;
				$indexBadge = 4;
				foreach ($badges as $badge) {
					/* @var $badgeEntity Badge */
					$badgeEntity = $badge['badge'];
					$badgeName = $badgeEntity->getName();
						
					/* @var $exercise Exercise */
					$exercise = $badge['resource']['resource']['exercise'];
	
					if ($badge['status'] == Badge::BADGE_STATUS_OWNED) {
						$nbOwnedBadges++;
					}

					$nbMaxTries = $badgeMaxTries[$badgeEntity->getId()];
					$marks = $badge['resource']['resource']['marks'];
					for ($i = 0; $i < $nbMaxTries; $i++) {
						$rowCSV[$index] = isset($marks[$i]) ? $marks[$i] : "";
						$index++;  
					}
					
					if ($badge['resource']['status'] == Badge::RES_STATUS_SUCCEED) {
						$rowCSV[$totalTries + $indexBadge] = "oui";
					} else if ($badge['resource']['status'] == Badge::RES_STATUS_FAILED) {
						$rowCSV[$totalTries + $indexBadge] = "non";
					} else {
						$rowCSV[$totalTries + $indexBadge] = "non";
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
			$badgesIndex = array();
			$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
			$workspaceUsers = $currentSession->getUsers();

			$headerCSV = array();
			$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
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
	 *     "{workspace}/subscriptions",
	 *     name="solerni_export_subscriptions_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportSubscriptionsStatsAction(AbstractWorkspace $workspace) {
		$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
		$from = $currentSession->getStartDate();
		$to = $currentSession->getEndDate();
		
		$now = new \DateTime();
		if ($now < $to) {
			$to = $now;
		}
		
		$rowsCSV = array();
		
		$headerCSV = array();

		$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$headerCSV[4] = "Subscription date";
		$headerCSV[5] = "Subscription time";
		$rowsCSV[] = $headerCSV;
		
		// Get information from database
		$logs = $this->logRepository->findAllBetween($workspace, $from, $to, "workspace-role-subscribe_user");
		$users = array();
		
		// Extract data
		foreach ($logs as $i => $log) {
			/* @var $log Log */
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
			$badgesIndex = array();
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
			$rowsCSV = array();
	
			$headerCSV = array();

			$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
			$headerCSV[4] = "Subscription date";
			$headerCSV[5] = "Last connection date";
			$rowsCSV[] = $headerCSV;
	
			// Extract data
			foreach ($users as $user) {
				// Get information from database
				$lastConnectionLog = $this->logRepository->getLastConnection($workspace, $user);
				$lastSubscriptionLog = $this->logRepository->getLastSubscription($workspace, $user);
	
			
				$rowCSV = array();
				$rowCSV[0] = $user->getLastName();
				$rowCSV[1] = $user->getFirstName();
				$rowCSV[2] = $user->getUsername();
				$rowCSV[3] = $user->getMail();
				if ($lastSubscriptionLog != null) {
					$rowCSV[4] = $lastSubscriptionLog->getDateLog()->format("d/m/Y");
				} else {
					$rowCSV[4] = "N/A";
				}
				if ($lastConnectionLog != null) {
					$rowCSV[5] = $lastConnectionLog->getDateLog()->format("d/m/Y");
				} else {
					$rowCSV[5] = "N/A";
				}
				$rowsCSV[] = $rowCSV;
			}
	

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
		$from = $currentSession->getStartDate();
		$to = $currentSession->getEndDate();
		
		$now = new \DateTime();
		if ($now < $to) {
			$to = $now;
		}
		
		$headerCSV = array();
		$header = array();

		$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$header[4] = $this->translator->trans('mooc_analytics_publisher_nb_pub', array(), 'platform');
		
		$headerCSV[] = $header;
		 

		$data = $this->analyticsManager->getForumStats($workspace, $from, $to);
		
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
		$headerCSV = array();
		$header = array();
	
		$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$header[4] = $this->translator->trans('mooc_analytics_users_nb_logs', array(), 'platform');
	
		$headerCSV[] = $header;
	
		$usersActivity = $this->analyticsManager->getMostActiveUsers($workspace);
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
		
		return $content;
	}
}
