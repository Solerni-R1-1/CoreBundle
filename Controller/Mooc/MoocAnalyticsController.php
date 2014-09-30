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
        $forumMostActiveSubjects = $this->analyticsManager->getMostActiveSubjects($workspace, 365, $excludeRoles);
        
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
        
        // Most active forum publishers
        $forumPublishers = $this->analyticsManager->getForumStats($workspace, $from, $to, $excludeRoles);
        $forumPublishersHeaders = array();
		$forumPublishersHeaders[0] = $this->translator->trans('mooc_analytics_user_name', array(), 'platform');
		$forumPublishersHeaders[1] = $this->translator->trans('mooc_analytics_user_firstname', array(), 'platform');
		$forumPublishersHeaders[2] = $this->translator->trans('mooc_analytics_user_username', array(), 'platform');
		$forumPublishersHeaders[3] = $this->translator->trans('mooc_analytics_user_mail', array(), 'platform');
		$forumPublishersHeaders[4] = $this->translator->trans('mooc_analytics_users_nb_published_posts', array(), 'platform');
        
        array_unshift( $forumPublishers, $forumPublishersHeaders );
        
        // Render
        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsDetails.html.twig',
            array(
                'workspace' => $workspace,
                'tabs'      => array(
                    'subscriptions_connections' => array(
                        'subscriptionStats' => array(
                            'graph_type'    => 'line-chart',
                            'description'   => 'subscriptionStatsDescription',
                            'x_data'        => array (
                                'x_renderer'    => 'date',
                                'x_label'       => 'date'
                            ), 
                            'graph_values'  => array(
                            	array(
                            		"y_label"   => "",
                            		"series"    => array(
		                            	"Inscriptions totales"  => $subscriptionStats[0],
		                            	"Inscriptions"          => $subscriptionStats[1]
    								)
                            	)
                           	)
                         ),
                        'activeUsers'       => array(
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
                                'x_label'       => 'hour_of_day'
                            ),
                            'graph_values'  => array(
                            	array(
                            		"y_label" => "",
                            		"series" => array(
                            			"Nombre de connections" => $hourlyAudience[0],
                            			"Activité sur le cours" => $hourlyAudience[1]
                            		)
                            	)
                            )
                        )
                    ),
                    'users' => array(
                        'mostActiveUsers'	=> array(
                            'graph_type'    => 'table',
                            'description'   => 'mostActiveUsersDescription',
                            'table_values'  => $mostActiveUsersWithHeader
                        ),
                        'forumPublishers'	=> array(
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
                                'x_label'       => 'date'
                            ),
                            'graph_values'  => array(
                            	array(
                            		"y_label" => "E",
                            		"series" => array(
                            			"Forum contributions" => $forumContributions[0]
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
     * @Route("/workspaces/{workspaceId}/open/tool/analytics/mooc/badges/piechart", name="claro_mooc_analytics_badges_piechart")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function analyticsMoocBadgesPieChartAction( $workspace, $user ) {
    	// Init the roles to filter the stats.
    	$excludeRoles = array();
    	$managerRole = $this->roleManager->getManagerRole($workspace);
    	$excludeRoles[] = $managerRole->getName();
    	$excludeRoles[] = "ROLE_ADMIN";
    	$excludeRoles[] = "ROLE_WS_CREATOR";
        
        $badgesSuccessRates = $this->analyticsManager->getBadgesSuccessRate($workspace, $excludeRoles);
        $badgesParticipationRates = $this->analyticsManager->getBadgesParticitpationRate($workspace, $excludeRoles);
        
        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsBadgesPiechart.html.twig',
            array(
                'workspace'             		=> $workspace,
                'badgesSuccessRates'    		=> $badgesSuccessRates,
            	'badgesParticipationRates'	=> $badgesParticipationRates
            )
        );
    }

    /**
     * @Route("/workspaces/{workspaceId}/open/tool/analytics/mooc/badges/chart", name="claro_mooc_analytics_badges_chart")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function analyticsMoocBadgesChartAction( $workspace, $user ) {
    	// Init the roles to filter the stats.
    	$excludeRoles = array();
    	$managerRole = $this->roleManager->getManagerRole($workspace);
    	$excludeRoles[] = $managerRole->getName();
    	$excludeRoles[] = "ROLE_ADMIN";
    	$excludeRoles[] = "ROLE_WS_CREATOR";
        
         $badgesSuccessRates = $this->analyticsManager->getBadgesSuccessRate($workspace, $excludeRoles);
        
        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsBadgesChart.html.twig',
            array(
                'workspace'      => $workspace,
                'badgesSuccessRates'    => $badgesSuccessRates
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
        
        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsExport.html.twig',
            array(
                'workspace'      => $workspace
            )
        );
    }
    
    
}
