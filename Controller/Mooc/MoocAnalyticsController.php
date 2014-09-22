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
    
    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router"),
     *     "translator"         = @DI\Inject("translator"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "mailManager"        = @DI\Inject("claroline.manager.mail_manager"),
     *     "moocService"        = @DI\Inject("orange.mooc.service"),
     *     "analyticsManager"   = @DI\Inject("claroline.manager.analytics_manager")
     * })
     */
    public function __construct( 
            SecurityContextInterface $security, 
            UrlGeneratorInterface $router, 
            TranslatorInterface $translator,
            WorkspaceManager $workspaceManager,
            MailManager $mailManager,
            MoocService $moocService,
            AnalyticsManager $analyticsManager
        ) {
        $this->translator = $translator;
        $this->security = $security;
        $this->router = $router;
        $this->workspaceManager = $workspaceManager;
        $this->mailManager = $mailManager;
        $this->moocService = $moocService;
        $this->analyticsManager = $analyticsManager;
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
    	$currentSession = $this->moocService->getActiveOrLastSessionFromWorkspace($workspace);
        $from = $currentSession->getStartDate();
        $to = $currentSession->getEndDate();
        $now = new \DateTime();
        if ($now < $to) {
        	$to = $now;
        }
        
    	$hourlyAudience = $this->analyticsManager->getHourlyAudience($workspace);
        $subscriptionStats = $this->analyticsManager->getSubscriptionsForPeriod($workspace, $from, $to);
        $forumContributions = $this->analyticsManager->getForumActivity($workspace, $from, $to);
        $activeUsers = $this->analyticsManager->getPercentageActiveMembers($workspace);
        $forumPublishers = $this->analyticsManager->getForumStats($workspace, $from, $to);
        
        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsDetails.html.twig',
            array(
                'hourlyAudience'        => $hourlyAudience,
                'subscriptionStats'     => $subscriptionStats,
                'forumContributions'    => $forumContributions,
            	'forumPublishers'		=> $forumPublishers,
                'activeUsers'           => $activeUsers,
                'workspace'             => $workspace
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
        
        $badgesSuccessRates = $this->analyticsManager->getBadgesSuccessRate($workspace);
        
        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\analytics:moocAnalyticsBadgesPiechart.html.twig',
            array(
                'workspace'             => $workspace,
                'badgesSuccessRates'    => $badgesSuccessRates
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
        
         $badgesSuccessRates = $this->analyticsManager->getBadgesSuccessRate($workspace);
        
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
