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

	/**
	 * @DI\InjectParams({
	 *     "badgeManager"            = @DI\Inject("claroline.manager.badge"),
	 *     "moocService"			 = @DI\Inject("orange.mooc.service"),
	 *     "container"				 = @DI\Inject("service_container")
	 *     })
	 */
	public function _construct(BadgeManager $badgeManager, $container, MoocService $moocService) {
		$this->setContainer($container);
		
		$this->badgeManager = $badgeManager;
		$this->moocService = $moocService;
		
		$this->logRepository = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Log\Log");
		$this->messageRepository = $this->getDoctrine()->getRepository("ClarolineForumBundle:Message");
		
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
			throw new \Exception();
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
	

			array_unshift($rowsCSV, $headerCSV);
			$content = $this->createCSVFromArray($rowsCSV);
			 
			rewind($handle);
			$content = stream_get_contents($handle);
			fclose($handle);
	
			return new Response($content, 200, array(
					'Content-Type' => 'application/force-download',
					'Content-Disposition' => 'attachment; filename="export.csv"'
			));
		} else {
			throw new \Exception();
		}
	}
	
	/**
	 * @EXT\Route(
	 *     "{workspace}/subscriptions/{from}/{to}",
	 *     name="solerni_export_subscriptions_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportSubscriptionsStatsAction(AbstractWorkspace $workspace, \DateTime $from, \DateTime $to) {
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
		
		// Extract data
		foreach ($logs as $i => $log) {
			/* @var $log Log */ 
			$rowCSV = array();
			$rowCSV[0] = $log->getDoer()->getLastName();
			$rowCSV[1] = $log->getDoer()->getFirstName();
			$rowCSV[2] = $log->getDoer()->getUsername();
			$rowCSV[3] = $log->getDoer()->getMail();
			$rowCSV[4] = $log->getDateLog()->format("d/m/Y");
			$rowCSV[5] = $log->getDateLog()->format("H:i:s");
			
			$rowsCSV[] = $rowCSV;
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
				$rowCSV[4] = $lastSubscriptionLog->getDateLog()->format("d/m/Y");
				$rowCSV[5] = $lastConnectionLog->getDateLog()->format("d/m/Y");
	
				$rowsCSV[] = $rowCSV;
			}
	

			$content = $this->createCSVFromArray($rowsCSV);
	
			return new Response($content, 200, array(
					'Content-Type' => 'application/force-download',
					'Content-Disposition' => 'attachment; filename="export.csv"'
			));
		} else {
			throw new \Exception();
		}
	}
	

	/**
	 * @EXT\Route(
	 *     "{workspace}/forum/publications/{from}/{to}",
	 *     name="solerni_export_forum_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportForumStatsAction(AbstractWorkspace $workspace, \DateTime $from, \DateTime $to) {
		if ($workspace->isMooc()) {
			$badgesIndex = array();
			$workspaceUsers = array();
			$mooc = $workspace->getMooc();
			
			$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
			$users = $session->getUsers();
			
			$rowsCSV = array();
	
			$headerCSV = array();
	
			$headerCSV[0] = "Firstname";
			$headerCSV[1] = "Lastname";
			$headerCSV[2] = "Username";
			$headerCSV[3] = "Mail";
			$headerCSV[4] = "Number of forum publications";
			
			$rowsCSV[] = $headerCSV;
	
			// Extract data
			foreach ($users as $user) {
				// Get information from database
				$nbPublications = $this->messageRepository->countMessagesForUser($session->getForum(), $user, $from, $to);
	
				$rowCSV = array();
				$rowCSV[0] = $user->getLastName();
				$rowCSV[1] = $user->getFirstName();
				$rowCSV[2] = $user->getUsername();
				$rowCSV[3] = $user->getMail();
				$rowCSV[4] = $nbPublications;
	
				$rowsCSV[] = $rowCSV;
			}

			$content = $this->createCSVFromArray($rowsCSV);
	
			return new Response($content, 200, array(
					'Content-Type' => 'application/force-download',
					'Content-Disposition' => 'attachment; filename="export.csv"'
			));
		} else {
			throw new \Exception();
		}
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
