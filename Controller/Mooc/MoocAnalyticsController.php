<?php


namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Repository\Mooc\MoocRepository;
use Claroline\CoreBundle\Repository\Mooc\MoocSessionRepository;
use Claroline\CoreBundle\Manager\MailManager;
use Icap\LessonBundle\Entity\Lesson;
use Claroline\CoreBundle\Controller\SolerniController;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Claroline\CoreBundle\Controller\Mooc\MoocService;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;

/**
 * Description of StaticController
 *
 * @author Simon Vart <svart@sii.fr>
 * 
 * @copyright 2014 @ sii.fr for Orange
 * 
 */

class MoocAnalyticsController extends Controller
{
    
    private $translator;
    private $security;
    private $router;
    private $workspaceManager;
    private $mailManager;
    private $moocService;
    private $analyticsManager;
    private $roleManager;
    
    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router"),
     *     "translator"         = @DI\Inject("translator"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "mailManager"        = @DI\Inject("claroline.manager.mail_manager"),
     *     "moocService"        = @DI\Inject("orange.mooc.service"),
     *     "analyticsManager"   = @DI\Inject("claroline.manager.analytics_manager"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct( 
            SecurityContextInterface $security, 
            UrlGeneratorInterface $router, 
            TranslatorInterface $translator,
            WorkspaceManager $workspaceManager,
            MailManager $mailManager,
            MoocService $moocService,
            AnalyticsManager $analyticsManager,
    		RoleManager $roleManager
        ) {
        $this->translator = $translator;
        $this->security = $security;
        $this->router = $router;
        $this->workspaceManager = $workspaceManager;
        $this->mailManager = $mailManager;
        $this->moocService = $moocService;
        $this->analyticsManager = $analyticsManager;
        $this->roleManager = $roleManager;
    }

    /**
     * @Route("/workspaces/{workspaceId}/open/tool/analytics/mooc/details", name="claro_mooc_analytics_details")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function analyticsMoocDetailsAction( $workspace, $user ) {
    	// Base parameters
    	$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
        $from = $currentSession->getStartDate();
        $to = $currentSession->getEndDate();
        $now = new \DateTime();
        if ($now < $to) {
        	$to = $now;
        }

        // Init the roles to filter the stats.
        $excludeRoles = array();
        $managerRole = $this->roleManager->getManagerRole($workspace);
        $excludeRoles[] = $managerRole->getName();
        $excludeRoles[] = "ROLE_ADMIN";
        $excludeRoles[] = "ROLE_WS_CREATOR";
        
        // Fetch all the necessary data
    	$hourlyAudience = $this->analyticsManager->getHourlyAudience($workspace, $excludeRoles);
        $subscriptionStats = $this->analyticsManager->getSubscriptionsForPeriod($workspace, $from, $to, $excludeRoles);
        $forumContributions = $this->analyticsManager->getForumActivity($workspace, $from, $to, $excludeRoles);
        $activeUsers = $this->analyticsManager->getPercentageActiveMembers($workspace, 5, $excludeRoles);
        
        // Most active users table
        $mostActiveUsers = $this->analyticsManager->getMostActiveUsers($workspace, $excludeRoles);
        $mostActiveUsersWithHeader = array();
        $row = array();
		$row[] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$row[] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$row[] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$row[] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$row[] = $this->translator->trans('mooc_analytics_users_nb_logs', array(), 'platform');
        $mostActiveUsersWithHeader[] = $row;
		foreach ($mostActiveUsers as $userActivity) {
			/* @var $user User */
			$user = $userActivity['user'];
			$row = array();
			$row[] = $user->getLastName();
			$row[] = $user->getFirstName();
			$row[] = $user->getUsername();
			$row[] = $user->getMail();
			$row[] = $userActivity['nbLogs'];
			$mostActiveUsersWithHeader[] = $row;
		}
        
        // Most active forum publishers table
        $forumPublishers = $this->analyticsManager->getForumStats($workspace, $from, $to, $excludeRoles);
        $forumPublishersHeaders = array();
		$forumPublishersHeaders[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$forumPublishersHeaders[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$forumPublishersHeaders[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$forumPublishersHeaders[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$forumPublishersHeaders[4] = $this->translator->trans('mooc_analytics_users_nb_published_posts', array(), 'platform');
        array_unshift( $forumPublishers, $forumPublishersHeaders );
        
        // Most active forum themes table
        $forumMostActiveSubjects = $this->analyticsManager->getMostActiveSubjects($workspace, 365, $excludeRoles);
        $forumMostActiveSubjectsHeaders = array();
        $forumMostActiveSubjectsHeaders[0] = $this->translator->trans('mooc_analytics_theme_name', array(), 'platform');
        $forumMostActiveSubjectsHeaders[1] = $this->translator->trans('mooc_analytics_theme_nb_posts', array(), 'platform');
        array_unshift($forumMostActiveSubjects, $forumMostActiveSubjectsHeaders);
        
        $totalUsers = $activeUsers[0] + $activeUsers[1];
        
        // Render
        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsWithSubTabs.html.twig',
            array(
                'workspace' => $workspace,
                'tabs'      => array(
                    'subscriptions_connections' => array(
                        'subscriptionStats' => array(
                            'export'        => array (
                                'solerni_export_subscriptions_stats' => 'export_subscriptions_stats'
                            ),
                            'graph_type'    => 'line-chart',
                            'description'   => 'subscriptionStatsDescription',
                            'x_data'        => array (
                                'x_renderer'    => 'date',
                                'x_label'       => 'Date'
                            ), 
                            'graph_values'  => array(
                            	array(
                            		"y_label"   => "Nombre d'inscriptions",
                            		"series"    => array(
		                            	"Inscriptions cumulées"     => $subscriptionStats[0],
		                            	"Inscriptions quotidiennes" => $subscriptionStats[1]
    								)
                            	)
                           	)
                         ),
                        'activeUsers'       => array(
                            'export'        => array (
                               'solerni_export_active_users_stats' => 'export_active_users'
                            ),
                            'key_data'      => "$totalUsers inscrits au MOOC.<br>$activeUsers[0] utilisateurs actifs au cours des 5 derniers jours.<br>$activeUsers[1] utilisateurs inactifs au cours des 5 derniers jours.",
                            'graph_type'    => 'pie-chart',
                            'description'   => 'activeUsersDescription',
                            'x_data'             => array (
                                'x_renderer'    => 'int',
                                'x_label'       => ''
                            ), 
                            'graph_values'  => array(
                            	array(
                            		"y_label" => "A",
                            		"series" => array(
                            			"Utilisateurs actifs"       => $activeUsers[0],
                                        "Utilisateurs non actifs"   => $activeUsers[1]
                            		)
                            	)
                            )
                        ),
                        'hourlyAudience'    => array(
                            'graph_type'    => 'line-chart',
                            'description'   => 'hourlyAudienceDescription',
                            'x_data'             => array (
                                'x_renderer'    => 'int',
                                'x_label'       => 'Heure de la journée'
                            ),
                            'graph_values'  => array(
                            	array(
                            		"y_label" => "Nombre d'évènements",
                            		"series" => array(
                            			"Entrées dans le MOOC" => $hourlyAudience[0],
                            			"Interactions dans le MOOC" => $hourlyAudience[1]
                            		)
                            	)
                            )
                        )
                    ),
                    'users' => array(
                        'mostActiveUsers'	=> array(
                            'export'        => array (
                                'solerni_export_users_activity' => 'export_users_activity'
                            ),
                            'graph_type'    => 'table',
                            'description'   => 'mostActiveUsersDescription',
                            'table_values'  => $mostActiveUsersWithHeader
                        ),
                        'forumPublishers'	=> array(
                            'export'        => array (
                                'solerni_export_forum_stats' => 'export_forum_stats'
                            ),
                            'graph_type'    => 'table',
                            'description'   => 'forumPublishersDescription',
                            'table_values'  => $forumPublishers
                        )
                    ),
                    'forum' => array(
                        'forumContributions'        => array(
                            'graph_type'    => 'line-chart',
                            'description'   => 'forumContributionsDescription',
                            'x_data'             => array (
                                'x_renderer'    => 'date',
                                'x_label'       => 'Date'
                            ),
                            'graph_values'  => array(
                            	array(
                            		"y_label" => "Nombre de contributions",
                            		"series" => array(
                            			"Contributions dans le forum" => $forumContributions[0]
                            		),
                                    "constants" => array(
                                        "mean" => $forumContributions[1]
                                    )
                            	)
                            )
                        ),
                        'forumMostActiveSubjects'	=> array(
                            'graph_type'        => 'table',
                            'description'       => 'forumMostActiveSubjectsDescription',
                            'table_values'      => $forumMostActiveSubjects
                        )
                    )
                )
            )
        );
    }
    
    /**
     * @Route("/workspaces/{workspaceId}/open/tool/analytics/mooc/badges/knowledge", name="claro_mooc_analytics_knowledge_badges")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function analyticsMoocKnowledgeBadgesAction( $workspace, $user ) {
    	// Init the roles to filter the stats.
    	$excludeRoles = array();
    	$managerRole = $this->roleManager->getManagerRole($workspace);
    	$excludeRoles[] = $managerRole->getName();
    	$excludeRoles[] = "ROLE_ADMIN";
    	$excludeRoles[] = "ROLE_WS_CREATOR";
        
        /*$badgesSuccessRates = $this->analyticsManager->getBadgesSuccessRate($workspace, $excludeRoles, false, true);
        $badgesParticipationRates = $this->analyticsManager->getBadgesParticipationRate($workspace, $excludeRoles, false, true);*/
    	$badgesRates = $this->analyticsManager->getBadgesRate($workspace, $excludeRoles, false, true);
    	$badgesSuccessRates = $badgesRates["success"];
    	$badgesParticipationRates = $badgesRates["participation"];
    	
        $tabs = array();
        // Extract knowledge badge from arrays
        foreach ($badgesSuccessRates as $badgeSuccessRates ) {
              if ( $badgeSuccessRates['type'] == 'knowledge' ) {
                  
                $tabs[$badgeSuccessRates['name']] = array(
                   'SuccessRateBadge_'.$badgeSuccessRates['id'] => array(
                        'graph_type' => 'pie-chart',
                        'description' => 'SuccessRateDescription',
                        'x_data' => array(
                                'x_renderer'    => 'date',
                                'x_label'       => 'Date' 
                        ),
                        'graph_values' => array(
                            array(
                                "y_label"   => "",
                                "series"    => array(
                                    'Réussite' => $badgeSuccessRates['success'],
                                    'Echec' => $badgeSuccessRates['failure'],
                                    'En cours' => $badgeSuccessRates['inProgress'],
                                    'Disponible' => $badgeSuccessRates['available']
                                )
                            )
                        )
                    ),
                    'ParticipationRateBadge_'.$badgeSuccessRates['id'] => array(
                        'graph_type' => 'line-chart',
                        'description' => 'ParticipationRateDescription',
                        'x_data' => array(
                                'x_renderer'    => 'date',
                                'x_label'       => 'Date' 
                        ),
                        'graph_values' => array(
                            array(
                                "y_label"   => "Nombre de participants",
                                "series"    => array(
                                    "Nombre cumulé d'utilisateurs" => $badgesParticipationRates[$badgeSuccessRates['id']]['data']['total'],
                                    "Inscriptions quotidiennes"    => $badgesParticipationRates[$badgeSuccessRates['id']]['data']['count']
                            )),
                            array (
                                "y_label"       => "Pourcentage",
                                "max"           => "100",
                                "min"           => "0",
                                "numberTicks"   => "10",
                                "tickInterval"  => "10",
                                "series"        => array(
                                    "Pourcentage d'utilisateurs du MOOC ayant participé au badge"   => $badgesParticipationRates[$badgeSuccessRates['id']]['data']['percentage']
                            ))
                        )  
                    )
                ); 
            }
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsWithSubTabs.html.twig',
            array(
                'workspace'     => $workspace,
                'tabs'          => $tabs
            )
        );
    }

    /**
     * @Route("/workspaces/{workspaceId}/open/tool/analytics/mooc/badges/skill", name="claro_mooc_analytics_skill_badges")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function analyticsMoocSkillBadgesAction( $workspace, $user ) {
    	// Init the roles to filter the stats.
    	$excludeRoles = array();
    	$managerRole = $this->roleManager->getManagerRole($workspace);
    	$excludeRoles[] = $managerRole->getName();
    	$excludeRoles[] = "ROLE_ADMIN";
    	$excludeRoles[] = "ROLE_WS_CREATOR";
        
        /*$badgesSuccessRates = $this->analyticsManager->getBadgesSuccessRate($workspace, $excludeRoles, true, false);
        $badgesParticipationRates = $this->analyticsManager->getBadgesParticipationRate($workspace, $excludeRoles, true, false);*/

    	$badgesRates = $this->analyticsManager->getBadgesRate($workspace, $excludeRoles, true, false);
    	$badgesSuccessRates = $badgesRates["success"];
    	$badgesParticipationRates = $badgesRates["participation"];
        
        $tabs = array();
        // Extract knopwledge badge from arrays
        foreach ($badgesSuccessRates as $badgeSuccessRates ) {
              if ( $badgeSuccessRates['type'] == 'skill' ) {
                  
                $tabs[$badgeSuccessRates['name']] = array(
                   'SuccessRateBadge_'.$badgeSuccessRates['id'] => array(
                        'graph_type' => 'pie-chart',
                        'description' => 'SuccessRateDescription',
                        'x_data' => array(
                                'x_renderer'    => 'date',
                                'x_label'       => 'Date' 
                        ),
                        'graph_values' => array(
                            array(
                                "y_label"   => "",
                                "series"    => array(
                                    'Réussite' => $badgeSuccessRates['success'],
                                    'Echec' => $badgeSuccessRates['failure'],
                                    'En cours' => $badgeSuccessRates['inProgress'],
                                    'Disponible' => $badgeSuccessRates['available']
                                )
                            )
                        )
                    ),
                    'ParticipationRateBadge_'.$badgeSuccessRates['id'] => array(
                        'graph_type' => 'line-chart',
                        'description' => 'ParticipationRateDescription',
                        'x_data' => array(
                                'x_renderer'    => 'date',
                                'x_label'       => 'Date' 
                        ),
                        'graph_values' => array(
                            array(
                                "y_label"   => "Nombre de participants",
                                "series"    => array(
                                    "Nombre cumulé d'utilisateurs" => $badgesParticipationRates[$badgeSuccessRates['id']]['data']['total'],
                                    "Inscriptions quotidiennes"    => $badgesParticipationRates[$badgeSuccessRates['id']]['data']['count']
                            )),
                            array (
                                "y_label"   => "Pourcentage",
                                "max"           => "100",
                                "min"           => "0",
                                "numberTicks"   => "10",
                                "tickInterval"  => "10",
                                "series"    => array(
                                    "Pourcentage d'utilisateurs du MOOC ayant participé au badge"   => $badgesParticipationRates[$badgeSuccessRates['id']]['data']['percentage']
                            ))
                        )  
                    )
                ); 
            }
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsWithSubTabs.html.twig',
            array(
                'workspace' => $workspace,
                'tabs'      => $tabs
            )
        );
    }
    
    /**
     * @Route("/workspaces/{workspaceId}/open/tool/analytics/mooc/export", name="claro_mooc_analytics_export")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function analyticsMoocExportAction( $workspace, $user ) {
        
        // route => libelle
        $exports = array(
            'solerni_export_subscriptions_stats'                    => 'export_subscriptions_stats',
            'solerni_export_active_users_stats'                     => 'export_active_users',
            'solerni_export_users_activity'                         => 'export_users_activity',
            'solerni_export_forum_stats'                            => 'export_forum_stats',
            'solerni_export_badges_knowledge_participation_stats'   => 'export_badge_participation_knowledge',
            'solerni_export_badges_knowledge_stats'                 => 'export_badge_knowledge',
            'solerni_export_badges_skill_participation_stats'       => 'export_badge_participation_skill',
            'solerni_export_badges_skill_stats'                     => 'export_badge_skill'
        );
        
        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsExport.html.twig',
            array(
                'workspace' => $workspace,
                'exports'   => $exports
            )
        );
    }
    
    
}
