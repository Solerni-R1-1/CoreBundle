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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Description of StaticController
 *
 * @author Kevin Danezis <kdanezis@sii.fr>
 * 
 * @copyright 2014 @ sii.fr for Orange
 *           
 */
class MoocController extends Controller
{
	private $patternId = '/^[0-9]+$/';
	private $patternName = '/^[0-9a-zA-Z\-\_](+)$/';
    
    private $translator;
    private $security;
    private $router;
    private $workspaceManager;
    private $mailManager;
    private $moocService;
    private $roleManager;
    
    
    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router"),
     *     "translator"         = @DI\Inject("translator"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "mailManager"        = @DI\Inject("claroline.manager.mail_manager"),
     *     "moocService"        = @DI\Inject("orange.mooc.service"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct( 
            SecurityContextInterface $security, 
            UrlGeneratorInterface $router, 
            TranslatorInterface $translator,
            WorkspaceManager $workspaceManager,
            MailManager $mailManager,
            MoocService $moocService,
    		RoleManager $roleManager
        ) {
        $this->translator = $translator;
        $this->security = $security;
        $this->router = $router;
        $this->workspaceManager = $workspaceManager;
        $this->mailManager = $mailManager;
        $this->moocService = $moocService;
        $this->roleManager = $roleManager;
    }

    /**
     * @Route("/{moocId}/{moocName}/sessions", name="mooc_view")
     * @EXT\ParamConverter(
     *      "mooc",
     *      class="ClarolineCoreBundle:Mooc\Mooc",
     *      options={"id" = "moocId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = false})
     */
    public function moocPageAction(Mooc $mooc, $user ) {
        
        if (  ! $mooc->isPublic()) {
        	/* redirect anon users to login if mooc is private */
        	if ($user == 'anon.') {
	            $url = $this->get('router')
	                         ->generate('claro_security_login');
	            return  $this->redirect($url);
        	} else if (!$this->roleManager->hasUserAccess($user, $mooc->getWorkspace())) {
        		throw new AccessDeniedHttpException();
        	}
        }

        $session = $this->moocService->getActiveOrNextSessionFromWorkspace( $mooc->getWorkspace(), $user );
        
        $nbUsers = $this->moocService->countUsersForSession($session);
		
        return $this->render(
            'ClarolineCoreBundle:Mooc:moocPresentation.html.twig',
            array(
                'mooc'      => $mooc,
                'session'   => $session,
                'user'      => $user,
            	'nbUsers'	=> $nbUsers
            )
        );
    }

    /**
     * Redirect the user towards another action depending of a keyword
     * 
     * 
     * @Route("/{moocId}/{moocName}/session/{sessionId}/{word}", name="mooc_view_session")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "moocSession",
     *      class="ClarolineCoreBundle:Mooc\MoocSession",
     *      options={"id" = "sessionId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "mooc",
     *      class="ClarolineCoreBundle:Mooc\Mooc",
     *      options={"id" = "moocId", "strictId" = true}
     * )
     */
    public function sessionPageAction( $mooc, $moocSession, $word, $user ){

    	$session = new Session();
    	$session->set($mooc->getId().'-moocSession', $moocSession->getId());
        if(!preg_match($this->patternId, $mooc->getId() )){
            return $this->inner404("parametre moocId invalid : ". $mooc->getId() );
        }

        if(!preg_match($this->patternId, $moocSession->getId() )){
            return $this->inner404("parametre sessionId invalid : " . $moocSession->getId() );
        }

        if($moocSession == null){
            return $this->inner404("la session n'existe pas : ".$moocSession->getId() );
        }

        if( $moocSession->getMooc()->getId() != $mooc->getId() ){
            return $this->inner404("mooc non correspondant . expected : "
                        .$session->getMooc()->getId()." given : " . $mooc->getId() );
        }


        switch ($word){
            case "apprendre" : 
                return $this->sessionApprendrePage($moocSession->getMooc(), $user);
                break;

            case "discuter" : 
                return $this->sessionDiscuterPage($moocSession);
                break;

            case "partager" :
                return $this->sessionPartagerPage($mooc->getWorkspace());
                break;

            case "subscribe" :
                return $this->sessionAddUserAction( $moocSession, $user );
                break;

            default:return $this->inner404("le word est inconnu : ".$word);
        }

    }

    /**
     * Display a error warning message in a Solerni interface
     */
    private function inner404($warn){
        return $this->render(
            'ClarolineCoreBundle:Mooc:mooc_error.html.twig',
            array(
                'warn'     => $warn
            )
        );
    }

    /**
     * 
     * Redirect user to the lesson object of the mooc
     * 
     */
    private function sessionApprendrePage( $mooc, $user ) {

        $node = $mooc->getLesson();

        if ( ! $node ) {
            return $this->inner404('La leçon n\'est pas définie pour ce mooc');
        }

        $lesson = $this->moocService->getLessonFromWorkspace( $mooc->getWorkspace(), $user);

        $url = $this->moocService->getRouteToTheLastChapter( $lesson, $user );
        return  $this->redirect($url);

    }

    /**
     * Redirect user to the forum object of the mooc session
     */
    private function sessionDiscuterPage($session) {

        $node = $session->getForum();

        if($node != null){
            $resourceType = $node->getResourceType()->getName();
            $nodeid = $node->getId();
            $url = $this->get('router')
                         ->generate('claro_resource_open', array('resourceType' => $resourceType, "node" => $nodeid));
            return  $this->redirect($url);
        }

        // 404
        $warn = "Aucun forum n'est associé à cette session";
        return $this->inner404($warn);

    }

    /**
     * Redirect user to the resource manager of the workspace
     */
    private function sessionPartagerPage($workspace) {

        $workspaceId = $workspace->getId();

        $url = $this->get('router')
                     ->generate('claro_workspace_open_tool', array('workspaceId' => $workspaceId, "toolName" => "resource_manager"));
        return  $this->redirect($url);

    }

    /**
     * @Route("/session/{sessionId}/subscribe", name="session_subscribe")
     * 
     * @EXT\ParamConverter(
     *      "moocSession",
     *      class="ClarolineCoreBundle:Mooc\MoocSession",
     *      options={"id" = "sessionId", "strictId" = true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = false })
     */
    public function sessionAddUserAction( $moocSession, $user ){

        /* if anon redirect to login page with query param to redirect user after login */
        if ( $user == 'anon.' ) {
        	$this->get('session')->set('moocSession', $moocSession);
            $route = $this->router->generate('claro_security_login', array () );
        } else {
            /* get all users */
            $users = $moocSession->getUsers();
            /* if not already in users, add user */
            if ( ! $users->contains( $user ) ) {
                /* add user to workspace if not already member */
                $workspace = $moocSession->getMooc()->getWorkspace();
                $userWorkspaces = $this->workspaceManager->getWorkspacesByUser( $user );
                $isRegistered = false;
                foreach( $userWorkspaces as $userWorkspace ) {
                    if ( $userWorkspace->getId() == $workspace->getId() ) {
                        $isRegistered = true;
                    }
                }
                if ( ! $isRegistered ) {
                    $this->workspaceManager->addUserAction( $workspace, $user );
                }
                /* add user to moocSession */
                $users->add( $user );
                $moocSession->setUsers( $users );
                $this->getDoctrine()->getManager()->persist($moocSession);
                $this->getDoctrine()->getManager()->flush();

                //Send an email
                if ($this->mailManager->isMailerAvailable()) {
                    $this->mailManager->sendInscriptionMoocMessage($user, $moocSession);
                }
            }

            /* redirect to lesson default page */
            $route = $this->router->generate('mooc_view', array ( 
                'moocId' => $moocSession->getMooc()->getId(), 
                'moocName' => $moocSession->getMooc()->getAlias(),
            	'showmodal' => true
                ) );
        }

        return new RedirectResponse($route);
    }

   /**
    * Render a session list (called from twig)
    * 
    * @ParamConverter("user", options={"authenticatedUser" = true })
    */
    public function getUserSessionsListAction( $user, $sessionComponentLayout = '2-column', $showUserProgression = false )
    {
        $userSession = $user->getMoocSessions();
        $groups = $user->getGroups();
        foreach ($groups as $group) {
        	foreach ($group->getMoocSessions() as $groupSession) {
        		$userSession[] = $groupSession;
        	}
        }

        $sessionsAvailable = array();
        if(count($userSession) == 0){
           $sessionsAvailable = $this->moocService->getAvailableSessionAroundToday(1500000, $user, 4); //15 days before / after
           //echo "OUI #".count($sessionsAvailable);
        } 



        return $this->render(
        'ClarolineCoreBundle:Mooc:moocSessionsList.html.twig',
        array(
            'sessions'                  => $userSession,
            'sessionsAvailable'         => $sessionsAvailable,
            'user'                      => $user,
            'sessionComponentLayout'    => $sessionComponentLayout,
            'showUserProgression'       => true
            )
        );
    }

    /**
     * Render a session Component (called from twig)
     * 
     * @ParamConverter("user", options={"authenticatedUser" = false })
     */
    public function renderSessionComponentAction( $session, $user, $sessionComponentLayout = '2-column', $showUserProgression = false )
    {

        // If we want progression
        if ( $showUserProgression && $user != 'anon.' ) {
            $progression = $this->moocService->getUserProgressionInLesson( $user, $session->getMooc()->getWorkspace() );
        } else {
            $progression = null;
        }

        return $this->render(
        'ClarolineCoreBundle:Mooc:moocSessionComponent.html.twig',
        array( 
            'session'                   => $session,
            'user'                      => $user,
            'progression'               => $progression,
            'sessionComponentLayout'    => $sessionComponentLayout
            )
        );
    }
    
    /**
     * Generate tabs on any mooc (onglet Apprendre, Discuter, etc) if the resource is set
     *
     * @ParamConverter("workspace",
     *                 class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *                 options={"id" = "workspaceId"})
     * @ParamConverter("user", options={"authenticatedUser" = false})
     */
    public function renderSolerniTabsAction(AbstractWorkspace $workspace, $user )
    {
        $router = $this->get('router');
        $solerniTabs = array(
            'solerniTabs' => array(),
            'workspace' => $workspace,
        );
        
        // Check Session
        $session = $this->moocService->getSessionForRegisteredUserFromWorkspace($workspace, $user);
        $currentUrl = $_SERVER['REQUEST_URI'];
        
        
        if ( $session ) {
            //get the mooc lesson
            $lesson = $this->moocService->getLessonFromWorkspace($workspace, $user);
            // get the mooc lesson link
            if ($lesson) {
                $firstSubChapter = $this->moocService->getFirstSubChapter($lesson);
                if ($firstSubChapter ) {
                    // generate tab
                    $url = $this->moocService->getRouteToTheLastChapter($lesson, $user);
                    $solerniTabs['solerniTabs'][] = array(
                        'name' => 'Apprendre',
                        'url' => $url,
                        'title' => 'Suivre les cours',
                    	'isSelected' => !(strpos(  $url, dirname($currentUrl) ) === false)
                    );
                }
            }
        }
        
        //get the session forum (only the last one)
        if ( $session ) {
            $forum = $this->getDoctrine()
                    ->getRepository( 'ClarolineForumBundle:Forum' )
                    ->findOneByResourceNode( $session->getForum() );
            // get the forum link
            if ($forum) {
                // generate tab
                $forumUrl = dirname(dirname($this ->get('router')->generate('claro_forum_categories', array('forum' => $forum->getId()))));
                $url = $router->generate('claro_forum_categories', array('forum' => $forum->getId()));
                $solerniTabs['solerniTabs'][] = array(
                    'name' => 'Discuter',
                    'url' => $url,
                    'title' => 'Participer au forum',
                   	'isSelected' => !(strpos(  dirname( $currentUrl ), $forumUrl ) === false)
                );
            }
        }
        
        // Generate tab for resource manager
        if ( $session ) {
            $url = $router->generate('claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'resource_manager'));
            $solerniTabs['solerniTabs'][] = array(
                'name' => 'Partager',
                'url' => $url,
                'title' => 'Accéder au gestionnaire de ressources',
                'isSelected' => !(strpos(  $currentUrl, $url ) === false)
            );
        }

        if ( $workspace->isMooc() ) {
        	$mooc = $workspace->getMooc();
        	$blogRes = $mooc->getBlog();
        	if ($blogRes != null) {
        		$blog = $this->getDoctrine()->getRepository('IcapBlogBundle:Blog')->findOneBy(array("resourceNode" => $blogRes));
        		$url = $router->generate('icap_blog_view', array('blogId' => $blog->getId()));
        		$solerniTabs['solerniTabs'][] = array(
        				'name' => 'S\'informer',
        				'url' => $url,
        				'title' => 'Accéder au blog',
        				'isSelected' => !(strpos($_SERVER['REQUEST_URI'], $url) === false)
        		);
        	}
        }
        
        $url = $router->generate('claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'my_badges'));
        $isMyBadgesPage = array( 'isMyBadgesPage' => !(strpos( $currentUrl, $url ) === false) );
        
        return $this->render(
            'ClarolineCoreBundle:Partials:includeSolerniTabs.html.twig',
            $solerniTabs + $isMyBadgesPage
        );
    }

    /**
     * 
     * Render presentation widget for the mooc (left column of designs) 
     * 
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"id" = "workspaceId"})
     * @ParamConverter("user", options={"authenticatedUser" = false})
     */
    public function getWorkspacePresentationWidgetAction( $workspace, $user, $renderProgression = true )
    {
        $lesson = $this->moocService->getLessonFromWorkspace($workspace, $user);

        if ( $renderProgression ) {
            $progression = $this->moocService->getUserProgressionInLesson($user, $workspace);
        } else {
           $progression = null; 
        }

        return $this->render(
            'ClarolineCoreBundle:Partials:workspacePresentationWidget.html.twig',
            array(
            'session' => $this->moocService->getSessionForRegisteredUserFromWorkspace($workspace, $user),
            'progression' => $progression,
            'workspace' => $workspace
            )
        );
    }
    
}
