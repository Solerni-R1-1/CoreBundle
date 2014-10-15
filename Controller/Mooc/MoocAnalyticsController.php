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
    	$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
        $from = $session->getStartDate();
        $to = $session->getEndDate();
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
    	$hourlyAudience = $this->analyticsManager->getHourlyAudience($session, $excludeRoles);
        $subscriptionStats = $this->analyticsManager->getSubscriptionsForPeriod($session);
        $forumContributions = $this->analyticsManager->getForumActivity($session, $from, $to, $excludeRoles);
        $activeUsers = $this->analyticsManager->getPercentageActiveMembers($session, 7, $excludeRoles);
        $connectionMean = $this->analyticsManager->getMeanNumberConnectionsDaily($session, $excludeRoles);
        
        // Most active users table
		$mostActiveUsers = $this->analyticsManager->getMostActiveUsers($session);
        $mostActiveUsersWithHeader = $mostActiveUsers;
        $row = array();
 		$row[] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
 		$row[] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
 		$row[] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
 		$row[] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
 		$row[] = $this->translator->trans('mooc_analytics_users_nb_logs', array(), 'platform');
 		array_unshift($mostActiveUsersWithHeader, $row);
        
        // Most active forum publishers table
        $forumPublishers = $this->analyticsManager->getForumStats($session);
        $forumPublishersHeaders = array();
		$forumPublishersHeaders[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$forumPublishersHeaders[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$forumPublishersHeaders[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$forumPublishersHeaders[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$forumPublishersHeaders[4] = $this->translator->trans('mooc_analytics_users_nb_published_posts', array(), 'platform');
        array_unshift( $forumPublishers, $forumPublishersHeaders );
        
        // Most active forum themes table
        $forumMostActiveSubjects = $this->analyticsManager->getMostActiveSubjects($session, 365, $excludeRoles);
        $forumMostActiveSubjectsHeaders = array();
        $forumMostActiveSubjectsHeaders[0] = $this->translator->trans('mooc_analytics_theme_name', array(), 'platform');
        $forumMostActiveSubjectsHeaders[1] = $this->translator->trans('mooc_analytics_theme_nb_posts', array(), 'platform');
        if (  !$forumMostActiveSubjects ) {
            $forumMostActiveSubjects = array();
        }
        array_unshift($forumMostActiveSubjects, $forumMostActiveSubjectsHeaders);
       
        $inactiveUsers = $activeUsers[1] - $activeUsers[0];
        
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
                        'connectionStats' => array(
                            'graph_type'    => 'line-chart',
                            'description'   => 'connectionStatsDescription',
                            'x_data'        => array (
                                'x_renderer'    => 'date',
                                'x_label'       => 'Date'
                            ), 
                            'graph_values'  => array(
                            	array(
                            		"y_label"   => "Nombre d'inscrits",
                            		"series"    => array(
		                            	"Nombre d'inscrits"     => $subscriptionStats[0]
    								)
                            	),
                                array (
                            		"y_label"   => "Nombre de connections",
                            		"series"    => array(
		                            	"Connections au MOOC"   => $subscriptionStats[2]
    								),
                                    "constants" => array(
                                        "mean" => $connectionMean
                                    )
                                )
                           	)
                         ),
                        'activeUsers'       => array(
                            'export'        => array (
                               'solerni_export_active_users_stats' => 'export_active_users'
                            ),
                            'key_data'      => "$activeUsers[1] inscrits au MOOC.<br>$activeUsers[0] utilisateurs actifs au cours des 7 derniers jours.<br>$inactiveUsers utilisateurs inactifs au cours des 7 derniers jours.",
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
                                        "Utilisateurs non actifs"   => $inactiveUsers
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
     * @Route("/workspaces/{workspaceId}/open/tool/analytics/mooc/badges/{badgeType}", name="claro_mooc_analytics_badges")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function analyticsMoocBadgesAction( $workspace, $user, $badgeType ) {
    	// Get session
    	$session = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
    	// Init the roles to filter the stats.
    	$excludeRoles = array();
    	$managerRole = $this->roleManager->getManagerRole($workspace);
    	$excludeRoles[] = $managerRole->getName();
    	$excludeRoles[] = "ROLE_ADMIN";
    	$excludeRoles[] = "ROLE_WS_CREATOR";
        
        $skillBadges        = ( $badgeType == 'skill' ) ? true : false;
        $knowledgeBadges    = ( $badgeType == 'knowledge' ) ? true : false;

    	$badgesRates = $this->analyticsManager->getBadgesRate($session, $excludeRoles, $skillBadges, $knowledgeBadges);
    	$badgesSuccessRates = $badgesRates["success"];
    	$badgesParticipationRates = $badgesRates["participation"];

        $tabs = array();
        // Extract knowledge badge from arrays
        foreach ($badgesSuccessRates as $badgeSuccessRates ) {
            $tabs[$badgeSuccessRates['name']] = $this->badgeTabContent( $badgeSuccessRates, $badgesParticipationRates );
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsWithSubTabs.html.twig',
            array(
                'workspace'     => $workspace,
                'tabs'          => $tabs
            )
        );
    }
    
    private function badgeTabContent( $badgeSuccessRates, $badgesParticipationRates ) 
    {

        $totalBadgeUsers    = $badgeSuccessRates['success'] + $badgeSuccessRates['failure'] + $badgeSuccessRates['inProgress'];
        $totalNonBadgeUsers =  $badgeSuccessRates['available'];
        $totalUsers = $totalBadgeUsers + $totalNonBadgeUsers;
        
        return array(
           'SuccessRateBadge_'.$badgeSuccessRates['id'] => array(
                'graph_type'    => 'pie-chart',
                'key_data'      => "$totalBadgeUsers participants au badge.<br> " . $badgeSuccessRates['success'] ." utilisateurs ont réussi.<br>" . $badgeSuccessRates['failure'] . " utilisateurs ont échoués.<br>" . $badgeSuccessRates['inProgress'] . " n'ont pas terminés.",
                'description'   => 'SuccessRateDescription',
                'x_data' => array(
                        'x_renderer'    => 'date',
                        'x_label'       => 'Date' 
                ),
                'graph_values' => array(
                    array(
                        "y_label"   => "",
                        "series"    => array(
                            'Réussite'      => $badgeSuccessRates['success'],
                            'Echec'         => $badgeSuccessRates['failure'],
                            'En cours'      => $badgeSuccessRates['inProgress']
                        )
                    )
                )
            ),
            'ParticipationEvolutionRateBadge_'.$badgeSuccessRates['id'] => array(
                'graph_type' => 'line-chart',
                'description' => 'ParticipationEvolutionRateDescription',
                'x_data' => array(
                        'x_renderer'    => 'date',
                        'x_label'       => 'Date' 
                ),
                'graph_values' => array(
                    array(
                        "y_label"   => "Nombre de participants",
                        "series"    => array(
                            "Nombre cumulé de participants au badge" => $badgesParticipationRates[$badgeSuccessRates['id']]['data']['total'],
                            "Nombre d'utilisateurs remplissant la première condition pour obtenir le badge"    => $badgesParticipationRates[$badgeSuccessRates['id']]['data']['count']
                    ))
                ) 
            ),
            'ParticipationRateBadge_'.$badgeSuccessRates['id'] => array(
                'graph_type'    => 'pie-chart',
                'key_data'      => "$totalUsers utilisateurs inscrits au MOOC.<br>$totalBadgeUsers participent au badge.<br>$totalNonBadgeUsers ne participent pas.",
                'description'   => 'ParticipationRateDescription',
                'x_data' => array(
                        'x_renderer'    => 'int',
                        'x_label'       => '' 
                ),
                'graph_values' => array(
                    array(
                        "y_label"   => "NA",
                        "series"    => array(
                            "utilisateurs participant au badge"         => $totalBadgeUsers,
                            "utilisateurs ne participant pas au badge"  => $badgeSuccessRates['available']
                    ))
                )  
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
            'solerni_export_users_general_stats'                    => 'export_users_validations_stats',
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
