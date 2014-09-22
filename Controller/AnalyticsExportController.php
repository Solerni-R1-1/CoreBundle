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
	
	
			$headerCSV = array();
			$headerCSV[0] = "LastName";
			$headerCSV[1] = "FirstName";
			$headerCSV[2] = "Username";
			$headerCSV[3] = "Mail";
			$indexBadges = 4;
			$rowsCSV = array();
	
			foreach ($workspaceUsers as $user) {
				/* @var $user User */
				$badges = $this->badgeManager->getAllBadgesForWorkspace($user, $workspace, true, false);
	
				$rowCSV = array();
	
				$rowCSV[0] = $user->getLastName();
				$rowCSV[1] = $user->getFirstName();
				$rowCSV[2] = $user->getUsername();
				$rowCSV[3] = $user->getMail();
	
				$nbOwnedBadges = 0;
				$totalNotes = 0;
				$nbNotes = 0;
	
				foreach ($badges as $badge) {
					/* @var $badgeEntity Badge */
					$badgeEntity = $badge['badge'];
					$badgeName = $badgeEntity->getName();
					if (!array_key_exists($badgeName, $badgesIndex)) {
						$index = $indexBadges;
						$badgesIndex[$badgeName] = $index;
						$headerCSV[$index] = $badgeName;
						$indexBadges++;
					} else {
						$index = $badgesIndex[$badgeName];
					}
						
					/* @var $drop Drop */
					$drop = $badge['resource']['resource']['drop'];
	
					if ($badge['status'] == Badge::BADGE_STATUS_OWNED) {
						$nbOwnedBadges++;
					}
	
					if ($badge['resource']['status'] == Badge::RES_STATUS_SUCCEED
							|| $badge['resource']['status'] == Badge::RES_STATUS_FAILED) {
								$rowCSV[$index] = $drop->getCalculatedGrade();
							} else {
								$rowCSV[$index] = 0;
							}
							$totalNotes += $rowCSV[$index];
							$nbNotes++;
				}
				 
				if ($nbNotes > 0) {
					$rowCSV[$indexBadges] = $totalNotes / $nbNotes;
				} else {
					$rowCSV[$indexBadges] = 0;
				}
				 
				$rowCSV[$indexBadges + 1] = $nbOwnedBadges;
				 
				$rowsCSV[] = $rowCSV;
				 
			}
	
	
			$headerCSV[$indexBadges] = "Mean on all badges";
			$headerCSV[$indexBadges + 1] = "Number of owned badges";

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
	
	
			$headerCSV = array();
			$headerCSV[0] = "LastName";
			$headerCSV[1] = "FirstName";
			$headerCSV[2] = "Username";
			$headerCSV[3] = "Mail";
			$indexBadges = 4;
			$rowsCSV = array();
	
			foreach ($workspaceUsers as $user) {
				/* @var $user User */
				$badges = $this->badgeManager->getAllBadgesForWorkspace($user, $workspace, false, true);
				$exerciseRepository = $this->getDoctrine()->getRepository("UJMExoBundle:Exercise");
	
				$rowCSV = array();
	
				$rowCSV[0] = $user->getLastName();
				$rowCSV[1] = $user->getFirstName();
				$rowCSV[2] = $user->getUsername();
				$rowCSV[3] = $user->getMail();
	
				$nbOwnedBadges = 0;
				$totalNotes = 0;
				$nbNotes = 0;
	
				foreach ($badges as $badge) {
					/* @var $badgeEntity Badge */
					$badgeEntity = $badge['badge'];
					$badgeName = $badgeEntity->getName();
					if (!array_key_exists($badgeName, $badgesIndex)) {
						$index = $indexBadges;
						$badgesIndex[$badgeName] = $index;
						$headerCSV[$index] = $badgeName;
						$indexBadges++;
					} else {
						$index = $badgesIndex[$badgeName];
					}
						
					/* @var $exercise Exercise */
					$exercise = $badge['resource']['resource']['exercise'];
	
					if ($badge['status'] == Badge::BADGE_STATUS_OWNED) {
						$nbOwnedBadges++;
					}
	
					if ($badge['resource']['status'] == Badge::RES_STATUS_SUCCEED
							|| $badge['resource']['status'] == Badge::RES_STATUS_FAILED) {
								$rowCSV[$index] = $badge['resource']['resource']['bestMark'];
							} else {
								$rowCSV[$index] = 0.00;
							}
							$totalNotes += $rowCSV[$index];
							$nbNotes++;
				}
				 
				if ($nbNotes > 0) {
					$rowCSV[$indexBadges] = $totalNotes / $nbNotes;
				} else {
					$rowCSV[$indexBadges] = 0.00;
				}
				 
				$rowCSV[$indexBadges + 1] = $nbOwnedBadges;
				 
				$rowsCSV[] = $rowCSV;
				 
			}

			$headerCSV[$indexBadges] = "Mean on all badges";
			$headerCSV[$indexBadges + 1] = "Number of owned badges";

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

		$headerCSV[0] = "Firstname";
		$headerCSV[1] = "Lastname";
		$headerCSV[2] = "Username";
		$headerCSV[3] = "Mail";
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
	
			$headerCSV[0] = "Firstname";
			$headerCSV[1] = "Lastname";
			$headerCSV[2] = "Username";
			$headerCSV[3] = "Mail";
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
		
		$header[0] = $this->translator->trans('mooc_analytics_publisher_name', array(), 'platform');
		$header[1] = $this->translator->trans('mooc_analytics_publisher_firstname', array(), 'platform');
		$header[2] = $this->translator->trans('mooc_analytics_publisher_username', array(), 'platform');
		$header[3] = $this->translator->trans('mooc_analytics_publisher_mail', array(), 'platform');
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
