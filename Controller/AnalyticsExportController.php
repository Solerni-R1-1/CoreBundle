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
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\EntityManager;


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

	private $userManager;
    private $entityManager;


	/**
	 * @DI\InjectParams({
	 *     "badgeManager"            = @DI\Inject("claroline.manager.badge"),
	 *     "moocService"			 = @DI\Inject("orange.mooc.service"),
	 *     "container"				 = @DI\Inject("service_container"),
	 *     "analyticsManager"		 = @DI\Inject("claroline.manager.analytics_manager"),
	 *     "roleManager"			 = @DI\Inject("claroline.manager.role_manager"),
	 *     "userManager"			 = @DI\Inject("claroline.manager.user_manager"),
     *     "entityManager"           = @DI\Inject("doctrine.orm.entity_manager")
	 *     })
	 */
	public function _construct(
			$container,
			BadgeManager $badgeManager,
			MoocService $moocService,
			AnalyticsManager $analyticsManager,
			RoleManager $roleManager,
			UserManager $userManager,
            EntityManager $entityManager) {

		$this->setContainer($container);
		$this->badgeManager         = $badgeManager;
		$this->moocService          = $moocService;
		$this->analyticsManager     = $analyticsManager;
		$this->roleManager          = $roleManager;
		$this->userManager          = $userManager;
		$this->logRepository        = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Log\Log");
		$this->messageRepository    = $this->getDoctrine()->getRepository("ClarolineForumBundle:Message");
		$this->userRepository       = $this->getDoctrine()->getRepository("ClarolineCoreBundle:User");
		$this->badgeRepository      = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Badge\Badge");
		$this->exoRepository        = $this->getDoctrine()->getRepository("UJMExoBundle:Exercise");
		$this->entityManager        = $entityManager;
		$this->translator           = $this->get('translator');

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

			$headerCSV = array();
			$headerCSV[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$headerCSV[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$headerCSV[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
			$indexBadges = 4;
			$rowsCSV = array();

			$usersBadges = array();


			$data = $this->badgeRepository->getSkillBadgesStats($workspace, $excludeRoles);

			$orderedData = array();
			$allBadges = array();
			foreach ($data as $datum) {
				$firstname		= $datum['user_firstname'];
				$lastname		= $datum['user_lastname'];
				$username		= $datum['user_username'];
				$mail			= $datum['user_mail'];
				$badgeName 		= $datum['badge_name'];
				$badgeId		= $datum['badge_id'];
				$expected		= $datum['expectedCorrections'];
				$corrections	= $datum['nbCorrections'];
				$mark			= $datum['mark'];
				$hasBadge		= ($datum['hasBadge'] == 1);

				if (!array_key_exists($mail, $orderedData)) {
					$orderedDatum = array();
					$orderedDatum['fn'] = $firstname;
					$orderedDatum['ln'] = $lastname;
					$orderedDatum['un'] = $username;
					$orderedDatum['ma'] = $mail;
					$orderedDatum['marks'] = array();
					$orderedDatum['badges'] = array();
					$orderedData[$mail] = $orderedDatum;
				}
				if ($expected <= $corrections) {
					$orderedData[$mail]['marks'][$badgeId] = $mark;
					$orderedData[$mail]['badges'][$badgeId] = $hasBadge;
				}
				if (!array_key_exists($badgeId, $allBadges)) {
					$allBadges[$badgeId] = $badgeName;
				}

			}
			ksort($allBadges);
			foreach ($allBadges as $badgeId => $badgeName) {
				$headerCSV[] = $badgeName;
			}
			foreach ($allBadges as $badgeId => $badgeName) {
				$headerCSV[] = $badgeName." obtenu ?";
			}

			$rowsCSV = array();
			foreach ($orderedData as $orderedDatum) {
				$rowCSV = array();
				$rowCSV[] = $orderedDatum['fn'];
				$rowCSV[] = $orderedDatum['ln'];
				$rowCSV[] = $orderedDatum['un'];
				$rowCSV[] = $orderedDatum['ma'];
				$marks = $orderedDatum['marks'];
				$badges = $orderedDatum['badges'];
				foreach ($allBadges as $badgeId => $badgeName) {
					if (array_key_exists($badgeId, $marks)) {
						$rowCSV[] = $marks[$badgeId];
					} else {
						$rowCSV[] = "";
					}
				}
				foreach ($allBadges as $badgeId => $badgeName) {
					if (array_key_exists($badgeId, $badges)) {
						$rowCSV[] = $badges[$badgeId] ? "oui" : "non";
					} else {
						$rowCSV[] = "non";
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
			//$workspaceUsers = $currentSession->getAllUsers($excludeRoles);

            if ( ! $currentSession ) {
                 $currentSession = $this->moocService->getActiveOrNextSessionFromWorkspace($workspace, null);
             }

             if (  ! $currentSession ) {
                 throw new NotFoundHttpException();
             }

			$headerCSV = array();
			$headerCSV[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$headerCSV[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$headerCSV[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
			$indexBadges = 4;
			$rowsCSV = array();

			if ($knowledgeBadges) {
				$dataK = $this->badgeRepository->getKnowledgeBadgesParticipations($currentSession, $excludeRoles);
			} else {
				$dataK = array();
			}

			if ($skillBadges) {
				$dataS = $this->badgeRepository->getSkillBadgesParticipations($currentSession, $excludeRoles);
			} else {
				$dataS = array();
			}

			$data = array_merge($dataK, $dataS);


			$allBadges = array();
			$orderedData = array();
			foreach ($data as $datum) {
				$firstname		= $datum['user_firstname'];
				$lastname		= $datum['user_lastname'];
				$username		= $datum['user_username'];
				$mail			= $datum['user_mail'];
				$badgeName 		= $datum['badge_name'];
				$badgeId		= $datum['badge_id'];
				$date			= $datum['date'];

				if (!array_key_exists($badgeId, $allBadges)) {
					$allBadges[$badgeId] = $badgeName;
				}

				if (!array_key_exists($mail, $orderedData)) {
					$orderedDatum = array();
					$orderedDatum['fn']						= $firstname;
					$orderedDatum['ln']						= $lastname;
					$orderedDatum['un']						= $username;
					$orderedDatum['ma']						= $mail;
					$orderedDatum['participations']			= array();
					$orderedData[$mail] = $orderedDatum;
				}

				$participations = &$orderedData[$mail]['participations'];
				$participations[$badgeId] = $date;
				ksort($participations);
			}

			ksort($allBadges);
			foreach ($allBadges as $badgeId => $badgeName) {
				$headerCSV[] = $badgeName;
			}

			foreach ($orderedData as $orderedDatum) {
				$rowCSV = array();
				$rowCSV[] = $orderedDatum['fn'];
				$rowCSV[] = $orderedDatum['ln'];
				$rowCSV[] = $orderedDatum['un'];
				$rowCSV[] = $orderedDatum['ma'];
				$participation = $orderedDatum['participations'];
				foreach ($allBadges as $badgeId => $badge) {
					if (array_key_exists($badgeId, $participation)) {
						$rowCSV[] = $participation[$badgeId];
					} else {
						$rowCSV[] = "N/A";
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
	 *     "{workspace}/users/general",
	 *     name="solerni_export_users_general_stats"
	 * )
	 *
	 * @param AbstractWorkspace $workspace
	 *
	 * @return Response
	 */
	public function exportUsersGeneralStatsAction(AbstractWorkspace $workspace) {

		$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);

        if ( ! $currentSession ) {
            $currentSession = $this->moocService->getActiveOrNextSessionFromWorkspace($workspace, null);
        }

        if (  ! $currentSession ) {
            throw new NotFoundHttpException();
        }

        $moocSessionId = $currentSession->getId();

		$headerCSV = array();
		$headerCSV[0] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$headerCSV[1] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$headerCSV[2] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$headerCSV[4] = $this->translator->trans('mooc_analytics_user_validated', array(), 'platform');
		$headerCSV[5] = $this->translator->trans('mooc_analytics_role', array(), 'platform');

        $sql = "SELECT last_name AS lastName,
                first_name AS firstName,
                username AS nickname,
                mail AS email,
                IF (is_validate = 1,'1','0') AS validate,
                IF(
                    id IN (
                        SELECT distinct u.id
                        FROM claro_user u
                        INNER JOIN claro_user_role ur ON u.id = ur.user_id
                        INNER JOIN claro_role r ON r.id = ur.role_id
                        WHERE r.name = 'ROLE_ADMIN' or r.name = 'ROLE_WS_CREATOR'
                        )
                ,'admin',IF(
                    id IN (
                        SELECT distinct u.id
                        FROM claro_user u
                        INNER JOIN claro_user_role ur ON u.id = ur.user_id
                        INNER JOIN claro_role r ON r.id = ur.role_id
                        WHERE r.name IN (
                            SELECT CONCAT('ROLE_WS_MANAGER_',guid) AS role_name_pedagogue
                            FROM claro_workspace w
                            INNER JOIN claro_mooc m ON w.id = m.workspace_id
                            INNER JOIN claro_mooc_session ms ON m.id = ms.mooc_id
                            WHERE ms.id = $moocSessionId
                            )
                        )
                ,'mooc_manager','collaborator'
                ) ) as role
            FROM (
                (
                    SELECT
                        DISTINCT u.id AS id,
                        u.last_name AS last_name,
                        u.first_name AS first_name,
                        u.username AS username,
                        u.mail AS mail,
                        u.is_validate AS is_validate,
                        u.is_enabled AS is_enabled
                        FROM claro_mooc_session ms
                        INNER JOIN claro_mooc m ON ms.mooc_id = m.id
                        INNER JOIN claro_workspace w ON m.workspace_id = w.id
                        INNER JOIN claro_role r ON w.id = r.workspace_id
                        INNER JOIN claro_user_mooc_session ums ON ms.id = ums.moocsession_id
                        INNER JOIN claro_user u ON ums.user_id = u.id
                        WHERE ms.id= $moocSessionId
                        AND u.id NOT IN (
                            SELECT distinct u.id
                            FROM claro_user u
                            INNER JOIN claro_user_role ur ON u.id = ur.user_id
                            INNER JOIN claro_role r ON r.id = ur.role_id
                            WHERE r.name = 'ROLE_ADMIN' -- or r.name = 'ROLE_WS_CREATOR'
                            )
                )
            UNION 	(
                    SELECT
                        DISTINCT u.id AS id,
                        u.last_name AS last_name,
                        u.first_name AS first_name,
                        u.username AS username,
                        u.mail AS mail,
                        u.is_validate AS is_validate,
                        u.is_enabled AS is_enabled
                        FROM claro_mooc_session ms
                        INNER JOIN claro_mooc m ON m.id = ms.mooc_id
                        INNER JOIN claro_group_mooc_session gms ON ms.id = gms.moocsession_id
                        INNER JOIN claro_user_group ug ON gms.group_id = ug.group_id
                        INNER JOIN claro_user u ON ug.user_id = u.id
                        INNER JOIN claro_group cg ON ug.group_id = cg.id
                        WHERE ms.id= $moocSessionId
                        AND u.id NOT IN (
                            SELECT distinct u.id
                            FROM claro_user u
                            INNER JOIN claro_user_role ur ON u.id = ur.user_id
                            INNER JOIN claro_role r ON r.id = ur.role_id
                            WHERE r.name = 'ROLE_ADMIN' -- or r.name = 'ROLE_WS_CREATOR'
                            )
                )
            ) AS tab_inscrits
            -- WHERE tab_inscrits.is_enabled = 1
            GROUP BY id
            ORDER BY validate ASC";

		$rowsCSV = $this->entityManager->getConnection()->fetchAll($sql);

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

        // Get session
		$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);

       if ( ! $currentSession ) {
            $currentSession = $this->moocService->getActiveOrNextSessionFromWorkspace($workspace, null);
        }

        if (  ! $currentSession ) {
            throw new NotFoundHttpException();
        }

        $moocSessionId = $currentSession->getId();

        // Get start and end date
		$from = $currentSession->getStartInscriptionDate();
		$to = $currentSession->getEndInscriptionDate();

        // Prepare export columns ($headerCSV)
		$rowsCSV = array();
		$headerCSV = array();

		$headerCSV[0]   = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$headerCSV[1]   = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$headerCSV[2]   = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$headerCSV[3]   = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$headerCSV[4]   = $this->translator->trans('mooc_analytics_user_subscriptiondate', array(), 'platform');
        $headerCSV[5]   = $this->translator->trans('mooc_analytics_user_subscriptiontime', array(), 'platform');
        $headerCSV[6]   = $this->translator->trans('mooc_analytics_role', array(), 'platform');
		$rowsCSV[]      = $headerCSV;

        $sql = "select last_name as lastName,
            first_name as firstName,
            username as nickname,
            mail as email,
            date(creation_date) as Subscription_date,
            time(creation_date) as Subscription_time,
            if(
                id IN (
                    SELECT distinct u.id
                    FROM claro_user u
                    INNER JOIN claro_user_role ur ON u.id = ur.user_id
                    INNER JOIN claro_role r ON r.id = ur.role_id
                    WHERE r.name = 'ROLE_ADMIN' or r.name = 'ROLE_WS_CREATOR'
                    )
            ,'admin',if(
                id IN (
                    SELECT distinct u.id
                    FROM claro_user u
                    INNER JOIN claro_user_role ur ON u.id = ur.user_id
                    INNER JOIN claro_role r ON r.id = ur.role_id
                    WHERE r.name IN (
                        SELECT CONCAT('ROLE_WS_MANAGER_',guid) AS role_name_pedagogue
                        FROM claro_workspace w
                        INNER JOIN claro_mooc m ON w.id = m.workspace_id
                        INNER JOIN claro_mooc_session ms ON m.id = ms.mooc_id
                        WHERE ms.id = $moocSessionId
                        )
                    )
            ,'mooc_manager','collaborator'
            ) ) as role
        from (
            (
                select
                    distinct u.id as id,
                    u.last_name as last_name,
                    u.first_name as first_name,
                    u.username as username,
                    u.mail as mail,
                    u.creation_date as creation_date
                    from claro_mooc_session ms
                    INNER JOIN claro_mooc m ON ms.mooc_id = m.id
                    INNER JOIN claro_workspace w ON m.workspace_id = w.id
                    INNER JOIN claro_role r ON w.id = r.workspace_id
                    INNER JOIN claro_user_mooc_session ums ON ms.id = ums.moocsession_id
                    INNER JOIN claro_user u ON ums.user_id = u.id
                    where ms.id= $moocSessionId
                    AND u.id NOT IN (
                        SELECT distinct u.id
                        FROM claro_user u
                        INNER JOIN claro_user_role ur ON u.id = ur.user_id
                        INNER JOIN claro_role r ON r.id = ur.role_id
                        WHERE r.name = 'ROLE_ADMIN' -- or r.name = 'ROLE_WS_CREATOR'
                        )
            )
        UNION 	(
                select
                    distinct u.id as id,
                    u.last_name as last_name,
                    u.first_name as first_name,
                    u.username as username,
                    u.mail as mail,
                    u.creation_date as creation_date
                    from claro_mooc_session ms
                    INNER JOIN claro_mooc m ON m.id = ms.mooc_id
                    INNER JOIN claro_group_mooc_session gms ON ms.id = gms.moocsession_id
                    INNER JOIN claro_user_group ug ON gms.group_id = ug.group_id
                    INNER JOIN claro_user u ON ug.user_id = u.id
                    INNER JOIN claro_group cg ON ug.group_id = cg.id
                    WHERE ms.id= $moocSessionId
                    AND u.id NOT IN (
                        SELECT distinct u.id
                        FROM claro_user u
                        INNER JOIN claro_user_role ur ON u.id = ur.user_id
                        INNER JOIN claro_role r ON r.id = ur.role_id
                        WHERE r.name = 'ROLE_ADMIN' -- or r.name = 'ROLE_WS_CREATOR'
                        )
            )
        )as tab_inscrits
        group by id
        order by creation_date desc ";

        $rows = $this->entityManager->getConnection()->fetchAll($sql);

        foreach ( $rows as $row ) {
            $orderedData[] = $row;
        }

		$rowsCSV = array_merge($rowsCSV, $orderedData);

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

            // Get session
            $currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);

           if ( ! $currentSession ) {
                $currentSession = $this->moocService->getActiveOrNextSessionFromWorkspace($workspace, null);
            }

            if (  ! $currentSession ) {
                throw new NotFoundHttpException();
            }

            $moocSessionId = $currentSession->getId();

            // Prepare export columns ($headerCSV)
            $rowsCSV = array();
            $headerCSV = array();
			$headerCSV[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
			$headerCSV[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
			$headerCSV[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
			$headerCSV[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
            $headerCSV[4] = $this->translator->trans('mooc_analytics_user_subscriptiondate', array(), 'platform');
			$headerCSV[5] = $this->translator->trans('mooc_analytics_user_lastconnectiondate', array(), 'platform');
            $headerCSV[6] = $this->translator->trans('mooc_analytics_role', array(), 'platform');
            $rowsCSV[]    = $headerCSV;

            $sql= " SELECT last_name AS lastName, first_name AS firstName,
                    username AS nickname, mail AS email, date(creation_date) AS Subscription_date,
                    -- time(creation_date) AS Subscription_time,
                    IFNULL(claro_user_action.LastConnectionDate,'N/A') AS LastConnectionDate,
                        IF( userid IN (
                            SELECT distinct u.id FROM claro_user u
                            INNER JOIN claro_user_role ur ON u.id = ur.user_id
                            INNER JOIN claro_role r ON r.id = ur.role_id
                            WHERE r.name = 'ROLE_ADMIN' or r.name = 'ROLE_WS_CREATOR'
                        ),'admin',
                        IF(
                            userid IN ( SELECT distinct u.id FROM claro_user u
                            INNER JOIN claro_user_role ur ON u.id = ur.user_id
                            INNER JOIN claro_role r ON r.id = ur.role_id
                            WHERE r.name IN (
                            SELECT CONCAT('ROLE_WS_MANAGER_',guid) AS role_name_pedagogue
                            FROM claro_workspace w
                            INNER JOIN claro_mooc m ON w.id = m.workspace_id
                            INNER JOIN claro_mooc_session ms ON m.id = ms.mooc_id
                            WHERE ms.id = $moocSessionId
                        )),'mooc_manager','collaborator') ) AS role
                            FROM (( SELECT DISTINCT u.id AS userid,
                            u.last_name AS last_name,
                            u.first_name AS first_name,
                            u.username AS username,
                            u.mail AS mail,
                            l.date_log AS creation_date -- CHANGE
                            FROM claro_mooc_session ms
                            INNER JOIN claro_mooc m ON ms.mooc_id = m.id
                            INNER JOIN claro_workspace w ON m.workspace_id = w.id
                            INNER JOIN claro_role r ON w.id = r.workspace_id
                            INNER JOIN claro_user_mooc_session ums ON ms.id = ums.moocsession_id
                            INNER JOIN claro_user u ON ums.user_id = u.id
                            INNER JOIN claro_log l ON w.id=l.workspace_id -- NEW
                            WHERE ms.id= $moocSessionId
                            AND l.action='workspace-role-subscribe_user' -- NEW
                            AND l.receiver_id = u.id -- NEW
                            AND u.id NOT IN (
                                SELECT distinct u.id
                                FROM claro_user u
                                INNER JOIN claro_user_role ur ON u.id = ur.user_id
                                INNER JOIN claro_role r ON r.id = ur.role_id
                                WHERE r.name = 'ROLE_ADMIN' -- OR r.name = 'ROLE_WS_CREATOR'
                            )
                        ORDER BY userid
                        )
                        UNION (
                            SELECT
                            DISTINCT u.id AS userid,
                            u.last_name AS last_name,
                            u.first_name AS first_name,
                            u.username AS username,
                            u.mail AS mail,
                            l.date_log AS creation_date -- CHANGE
                            FROM claro_mooc_session ms
                            INNER JOIN claro_mooc m ON m.id = ms.mooc_id
                            INNER JOIN claro_group_mooc_session gms ON ms.id = gms.moocsession_id
                            INNER JOIN claro_user_group ug ON gms.group_id = ug.group_id
                            INNER JOIN claro_user u ON ug.user_id = u.id
                            INNER JOIN claro_group cg ON ug.group_id = cg.id
                            INNER JOIN claro_log l ON gms.group_id=l.receiver_group_id -- NEW
                            WHERE ms.id= $moocSessionId
                            AND l.action='workspace-role-subscribe_group' -- OR (l.receiver_id = u.id AND l.action='group-add_user') -- NEW
                            AND u.id NOT IN (
                            SELECT distinct u.id
                            FROM claro_user u
                            INNER JOIN claro_user_role ur ON u.id = ur.user_id
                            INNER JOIN claro_role r ON r.id = ur.role_id
                            WHERE r.name = 'ROLE_ADMIN' -- OR r.name = 'ROLE_WS_CREATOR'
                        )
                        ORDER BY userid
                        )
                        )AS tab_inscrits
                            LEFT OUTER JOIN (
                            SELECT cl.doer_id, max(date_log) as LastConnectionDate
                            FROM claro_log cl
                            WHERE cl.workspace_id IN (
                            SELECT m.workspace_id
                            FROM claro_mooc m
                            INNER JOIN claro_mooc_session ms ON ms.mooc_id = m.id
                            WHERE ms.id= $moocSessionId
                        )
                            AND cl.action !='workspace-role-subscribe_user'
                            AND cl.action !='workspace-role-unsubscribe_user'
                            AND cl.action !='workspace-role-subscribe_group'
                            AND cl.action !='workspace-role-unsubscribe_group'
                            GROUP BY cl.doer_id
                            ORDER BY cl.doer_id
                        ) AS claro_user_action ON claro_user_action.doer_id = tab_inscrits.userid
                    GROUP BY tab_inscrits.userid
                    ORDER BY claro_user_action.LastConnectionDate DESC";

            $rows = $this->entityManager->getConnection()->fetchAll($sql);

            foreach ( $rows as $row ) {
                $orderedData[] = $row;
            }

            $rowsCSV = array_merge($rowsCSV, $orderedData);

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

       if ( ! $currentSession ) {
            $currentSession = $this->moocService->getActiveOrNextSessionFromWorkspace($workspace, null);
        }

        if (  ! $currentSession ) {
            throw new NotFoundHttpException();
        }

        $moocSessionId = $currentSession->getId();

		$headerCSV = array();
		$header = array();

		$header[0]      = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$header[1]      = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$header[2]      = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$header[3]      = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$header[4]      = $this->translator->trans('mooc_analytics_publisher_nb_pub', array(), 'platform');
        $header[6]      = $this->translator->trans('mooc_analytics_role', array(), 'platform');
		$headerCSV[]    = $header;

        $sql = "SELECT last_name AS lastName,
            first_name AS firstName,
            username AS nickname,
            mail AS email,
            IFNULL(tab_messages_per_doer.nbMessagesOnMOOCforum,0) AS nbMessagesOnMOOCforum,
            if(
                userid IN (
                    SELECT DISTINCT u.id
                    FROM claro_user u
                    INNER JOIN claro_user_role ur ON u.id = ur.user_id
                    INNER JOIN claro_role r ON r.id = ur.role_id
                    WHERE r.name = 'ROLE_ADMIN' OR r.name = 'ROLE_WS_CREATOR'
                    )
            ,'admin',if(
                userid IN (
                    SELECT distinct u.id
                    FROM claro_user u
                    INNER JOIN claro_user_role ur ON u.id = ur.user_id
                    INNER JOIN claro_role r ON r.id = ur.role_id
                    WHERE r.name IN (
                        SELECT CONCAT('ROLE_WS_MANAGER_',guid) AS role_name_pedagogue
                        FROM claro_workspace w
                        INNER JOIN claro_mooc m ON w.id = m.workspace_id
                        INNER JOIN claro_mooc_session ms ON m.id = ms.mooc_id
                        WHERE ms.id = $moocSessionId
                        )
                    )
            ,'mooc_manager','collaborator'
            ) ) AS role
        FROM (
            (
                SELECT
                    DISTINCT u.id AS userid,
                    u.last_name AS last_name,
                    u.first_name AS first_name,
                    u.username AS username,
                    u.mail AS mail,
                    u.creation_date AS creation_date
                    FROM claro_mooc_session ms
                    INNER JOIN claro_mooc m ON ms.mooc_id = m.id
                    INNER JOIN claro_workspace w ON m.workspace_id = w.id
                    INNER JOIN claro_role r ON w.id = r.workspace_id
                    INNER JOIN claro_user_mooc_session ums ON ms.id = ums.moocsession_id
                    INNER JOIN claro_user u ON ums.user_id = u.id
                    WHERE ms.id= $moocSessionId
                    AND u.id NOT IN (
                        SELECT distinct u.id
                        FROM claro_user u
                        INNER JOIN claro_user_role ur ON u.id = ur.user_id
                        INNER JOIN claro_role r ON r.id = ur.role_id
                        WHERE r.name = 'ROLE_ADMIN' -- OR r.name = 'ROLE_WS_CREATOR'
                        )
            )
        UNION 	(
                SELECT
                    DISTINCT u.id AS userid,
                    u.last_name AS last_name,
                    u.first_name AS first_name,
                    u.username AS username,
                    u.mail AS mail,
                    u.creation_date AS creation_date
                    FROM claro_mooc_session ms
                    INNER JOIN claro_mooc m ON m.id = ms.mooc_id
                    INNER JOIN claro_group_mooc_session gms ON ms.id = gms.moocsession_id
                    INNER JOIN claro_user_group ug ON gms.group_id = ug.group_id
                    INNER JOIN claro_user u ON ug.user_id = u.id
                    INNER JOIN claro_group cg ON ug.group_id = cg.id
                    WHERE ms.id= $moocSessionId
                    AND u.id NOT IN (
                        SELECT distinct u.id
                        FROM claro_user u
                        INNER JOIN claro_user_role ur ON u.id = ur.user_id
                        INNER JOIN claro_role r ON r.id = ur.role_id
                        WHERE r.name = 'ROLE_ADMIN' -- OR r.name = 'ROLE_WS_CREATOR'
                        )
            )
        )AS tab_inscrits
        LEFT OUTER JOIN
        (SELECT
                cfm.user_id,
                COUNT(cfm.id) AS nbMessagesOnMOOCforum
            FROM claro_forum_message cfm
            INNER JOIN claro_forum_subject cfs ON cfs.id = cfm.subject_id
            INNER JOIN claro_forum_category cfc ON cfc.id = cfs.category_id
            INNER JOIN claro_forum cf ON cf.id = cfc.forum_id
            INNER JOIN claro_mooc_session cms ON cms.forum_id = cf.resourceNode_id
            WHERE
                cms.id=$moocSessionId
                AND cfm.user_id NOT IN (
                    SELECT distinct u.id
                    FROM claro_user u
                    INNER JOIN claro_user_role ur ON u.id = ur.user_id
                    INNER JOIN claro_role r ON r.id = ur.role_id
                    WHERE r.name = 'ROLE_ADMIN' -- OR r.name = 'ROLE_WS_CREATOR'
                )
            GROUP BY cfm.user_id
        ) AS tab_messages_per_doer ON tab_messages_per_doer.user_id=tab_inscrits.userid
        ORDER BY nbMessagesOnMOOCforum desc";

        $rows = $this->entityManager->getConnection()->fetchAll($sql);

        foreach ( $rows as $row ) {
            $orderedData[] = $row;
        }

        $rowsCSV = array_merge($headerCSV, $orderedData);

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
		$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);

       if ( ! $currentSession ) {
            $currentSession = $this->moocService->getActiveOrNextSessionFromWorkspace($workspace, null);
        }

        if (  ! $currentSession ) {
            throw new NotFoundHttpException();
        }

        $moocSessionId = $currentSession->getId();

		$headerCSV = array();
		$header = array();

		$header[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$header[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$header[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$header[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$header[4] = $this->translator->trans('mooc_analytics_users_nb_logs', array(), 'platform');
        $header[6] = $this->translator->trans('mooc_analytics_role', array(), 'platform');
		$headerCSV[]    = $header;

         $sql = "SELECT last_name AS lastName,
            first_name AS firstName,
            username AS nickname,
            mail AS email,
            IFNULL(tab_activity_per_doer.nbActivityOnMOOC,0) AS nbActivityOnMOOC,
            if(
                userid IN (
                    SELECT DISTINCT u.id
                    FROM claro_user u
                    INNER JOIN claro_user_role ur ON u.id = ur.user_id
                    INNER JOIN claro_role r ON r.id = ur.role_id
                    WHERE r.name = 'ROLE_ADMIN' OR r.name = 'ROLE_WS_CREATOR'
                    )
            ,'admin',if(
                userid IN (
                    SELECT DISTINCT u.id
                    FROM claro_user u
                    INNER JOIN claro_user_role ur ON u.id = ur.user_id
                    INNER JOIN claro_role r ON r.id = ur.role_id
                    WHERE r.name IN (
                        SELECT CONCAT('ROLE_WS_MANAGER_',guid) AS role_name_pedagogue
                        FROM claro_workspace w
                        INNER JOIN claro_mooc m ON w.id = m.workspace_id
                        INNER JOIN claro_mooc_session ms ON m.id = ms.mooc_id
                        WHERE ms.id = $moocSessionId
                        )
                    )
            ,'mooc_manager','collaborator'
            ) ) AS role
        FROM (
            (
                SELECT
                    DISTINCT u.id AS userid,
                    u.last_name AS last_name,
                    u.first_name AS first_name,
                    u.username AS username,
                    u.mail AS mail,
                    u.creation_date AS creation_date
                    FROM claro_mooc_session ms
                    INNER JOIN claro_mooc m ON ms.mooc_id = m.id
                    INNER JOIN claro_workspace w ON m.workspace_id = w.id
                    INNER JOIN claro_role r ON w.id = r.workspace_id
                    INNER JOIN claro_user_mooc_session ums ON ms.id = ums.moocsession_id
                    INNER JOIN claro_user u ON ums.user_id = u.id
                    WHERE ms.id= $moocSessionId
                    AND u.id NOT IN (
                        SELECT distinct u.id
                        FROM claro_user u
                        INNER JOIN claro_user_role ur ON u.id = ur.user_id
                        INNER JOIN claro_role r ON r.id = ur.role_id
                        WHERE r.name = 'ROLE_ADMIN' -- or r.name = 'ROLE_WS_CREATOR'
                        )
            )
        UNION 	(
                SELECT
                    DISTINCT u.id AS userid,
                    u.last_name AS last_name,
                    u.first_name AS first_name,
                    u.username AS username,
                    u.mail AS mail,
                    u.creation_date AS creation_date
                    FROM claro_mooc_session ms
                    INNER JOIN claro_mooc m ON m.id = ms.mooc_id
                    INNER JOIN claro_group_mooc_session gms ON ms.id = gms.moocsession_id
                    INNER JOIN claro_user_group ug ON gms.group_id = ug.group_id
                    INNER JOIN claro_user u ON ug.user_id = u.id
                    INNER JOIN claro_group cg ON ug.group_id = cg.id
                    WHERE ms.id= $moocSessionId
                    AND u.id NOT IN (
                        SELECT distinct u.id
                        FROM claro_user u
                        INNER JOIN claro_user_role ur ON u.id = ur.user_id
                        INNER JOIN claro_role r ON r.id = ur.role_id
                        WHERE r.name = 'ROLE_ADMIN' -- OR r.name = 'ROLE_WS_CREATOR'
                        )
            )
        )AS tab_inscrits
        LEFT OUTER JOIN
        (SELECT
                doer_id,
                COUNT(cl.doer_id) AS nbActivityOnMOOC
            FROM claro_log cl
            INNER JOIN claro_workspace cw ON cw.id = cl.workspace_id
            INNER JOIN claro_mooc cm ON cm.workspace_id = cw.id
            INNER JOIN claro_mooc_session cms ON cms.mooc_id = cm.id
            WHERE
                cms.id=$moocSessionId
                AND cl.action NOT IN (
                    'workspace-role-subscribe_user',
                    'workspace-role-unsubscribe_user',
                    'workspace-role-subscribe_group',
                    'workspace-role-unsubscribe_group')
                AND cl.date_log >= cms.start_inscription_date -- better start_date ??
                AND cl.date_log <= cms.end_date
                AND cl.doer_id NOT IN (
                    SELECT distinct u.id
                    FROM claro_user u
                    INNER JOIN claro_user_role ur ON u.id = ur.user_id
                    INNER JOIN claro_role r ON r.id = ur.role_id
                    WHERE r.name = 'ROLE_ADMIN' -- OR r.name = 'ROLE_WS_CREATOR'
                )
            GROUP BY doer_id
        ) AS tab_activity_per_doer ON tab_activity_per_doer.doer_id=tab_inscrits.userid
        ORDER BY nbActivityOnMOOC desc";

        $rows = $this->entityManager->getConnection()->fetchAll($sql);

        foreach ( $rows as $row ) {
            $orderedData[] = $row;
        }

        $rowsCSV = array_merge($headerCSV, $orderedData);

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
