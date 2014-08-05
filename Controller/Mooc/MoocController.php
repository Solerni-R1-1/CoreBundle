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
use Icap\LessonBundle\Entity\Lesson;
use Claroline\CoreBundle\Controller\SolerniController;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Manager\WorkspaceManager;

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
    
    
    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router"),
     *     "translator"         = @DI\Inject("translator"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct( 
            SecurityContextInterface $security, 
            UrlGeneratorInterface $router, 
            TranslatorInterface $translator,
            WorkspaceManager $workspaceManager
        ) {
        $this->translator = $translator;
        $this->security = $security;
        $this->router = $router;
        $this->workspaceManager = $workspaceManager;
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
    public function moocPageAction( $mooc, $user ) {

        //Check pattern
        if(  ( ! preg_match ( $this->patternId, $mooc->getId() ) ) || ! $mooc ) {
            return $this->inner404("MoocId invalid : " . $mooc->getId() );
        }

        $sessions = $mooc->getMoocSessions();

        return $this->render(
            'ClarolineCoreBundle:Mooc:mooc.html.twig',
            array(
                'mooc'      => $mooc,
                'sessions'  => $sessions,
                'user'      => $user
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
     * Redirect user to the lesson object of the mooc
     */
    private function sessionApprendrePage($mooc, $user) {

        $node = $mooc->getLesson();

        if ( ! $node ) {
            return $this->inner404('La leçon n\'est pas définie pour ce mooc');
        }

        $repo = $this->getDoctrine()->getRepository('IcapLessonBundle:Lesson');
        $lesson = $repo->findOneByResourceNode($node);

        $url = $this->getRouteToTheLastChapter($lesson, $user);
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
            $route = $this->router->generate('claro_security_login', array ( 'mooc_session_id' => $moocSession->getId() ) );
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
            }
            /* redirect to lesson default page */
            $route = $this->router->generate('mooc_view_session', array ( 
                'moocId' => $moocSession->getMooc()->getId(), 
                'moocName' => $moocSession->getMooc()->getAlias(),
                'sessionId' => $moocSession->getId(), 
                'word' => 'apprendre'
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

        return $this->render(
        'ClarolineCoreBundle:Mooc:moocSessionsList.html.twig',
        array( 
            'sessions'                  => $userSession,
            'user'                      => $user,
            'sessionComponentLayout'    => $sessionComponentLayout,
            'showUserProgression'       => true
            )
        );
    }

    /**
     * Render a session Component (called from twig)
     * 
     * @ParamConverter("user", options={"authenticatedUser" = true })
     */
    public function renderSessionComponentAction( $session, $user, $sessionComponentLayout = '2-column', $showUserProgression = false )
    {

        // If we want progression
        if ( $showUserProgression ) {
            $doctrine = $this->getDoctrine();
            $chapterRepository = $doctrine->getRepository('IcapLessonBundle:Chapter');
            $doneRepository = $doctrine->getRepository('IcapLessonBundle:Done');
            $lessonRepository = $this->getDoctrine()->getRepository('IcapLessonBundle:Lesson');

            /* get lesson progression */
            $lessonNode = $session->getMooc()->getLesson();
            $lesson = $lessonRepository->findOneByResourceNode($lessonNode);
            $totalProgression = 0;
            $currentProgression = 0;
            $allChapters = $chapterRepository->findByLesson( array('lesson' => $lesson), array('left' => 'ASC'));
            $firstChapter = null;
            foreach($allChapters as $chapter){
                if($chapter->getLevel() > 1){
                    if($firstChapter == null){
                        $firstChapter = $chapter;
                    }
                    $done = $doneRepository->find(array('lesson' => $chapter->getId(), 'user' => $user->getId()));
                    if($done && $done->getDone()){
                        $currentProgression++;
                    }
                    $totalProgression++;
                }
            }

            $progression = ($totalProgression == 0) ? 0 : round($currentProgression / $totalProgression * 100);
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
     * GENERATE TABS
     *      - NEED TO FIND CURRENT WORKSPACE
     *      - if time : ability to choose a resource from a resource list
     *      - NEED TO CALCULATE URL FROM A RESOURCE ROUTE ( evaluate how to configure )
     *
     * @ParamConverter("workspace",
     *                 class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *                 options={"id" = "workspaceId"})
     * @ParamConverter("user", options={"authenticatedUser" = false})
     */
    public function renderSolerniTabsAction( $workspace, $user )
    {
        
        $solerniTabs = array(
            'solerniTabs' => array(),
            'workspace' => $workspace,
        );

        //get the mooc lesson
        $lesson = $this->getDoctrine()->getRepository( 'IcapLessonBundle:Lesson' )->findOneByResourceNode( $workspace->getMooc()->getLesson() );
        // get the mooc lesson link
        if ($lesson) {
            $firstSubChapter = $this->getFirstSubChapter($lesson);
            if ($firstSubChapter ) {
                // generate tab
                $solerniTabs['solerniTabs'][] = array(
                    'name' => 'Apprendre',
                    'url' => $this->getRouteToTheLastChapter($lesson, $user),
                    'title' => 'Suivre les cours'
                );
            }
        }
        
        //get the session forum (only the last one)
        $moocSessions = $workspace->getMooc()->getMoocSessions();
        foreach( $moocSessions as $moocSession ) {
            $forumResourceNode = $moocSession->getForum();
        }
        
        $forum = $this->getDoctrine()->getRepository( 'ClarolineForumBundle:Forum' )->findOneByResourceNode( $forumResourceNode );
        // get the forum link
        if ($forum) {
            // generate tab
            $solerniTabs['solerniTabs'][] = array(
                'name' => 'Discuter',
                'url' => $this ->get('router')->generate('claro_forum_categories', array('forum' => $forum->getId())),
                'title' => 'Participer au forum'
            );
        }
        
        // Generate tab for resource manager
        $solerniTabs['solerniTabs'][] = array(
            'name' => 'Partager',
            'url' => $this->get('router')->generate('claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'resource_manager')),
            'title' => 'Accéder au gestionnaire de ressources'
        );
                
        return $this->render(
            'ClarolineCoreBundle:Partials:includeSolerniTabs.html.twig',
            $solerniTabs
        );
    }
    
    /**
     * @param Lesson $lesson
     *
     * @return \Icap\LessonBundle\Entity\Chapter
     */
    protected function getFirstSubChapter( \Icap\LessonBundle\Entity\Lesson $lesson )
    {
        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $firstChapter = $chapterRepository->getFirstChapter($lesson);
        $subChapter = null;
        if ($firstChapter) {
            $subChapter = $chapterRepository->getChapterFirstChild($firstChapter);
        }
        
        return $subChapter;
    }
    
    /**
     * get the route to the last chapter read from a lesson, according to the log.
     *
     * @param Lesson $lesson
     * @param User|string $user
     * @return string
     */
    private function getRouteToTheLastChapter( \Icap\LessonBundle\Entity\Lesson $lesson, $user )
    {
        $router = $this->get('router');
        $doctrine = $this->get('doctrine');
        $logRepository = $doctrine->getRepository('ClarolineCoreBundle:Log\Log');
        $chapterRepository = $doctrine->getRepository('IcapLessonBundle:Chapter');
        $resourceType = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('icap_lesson');
        if ($resourceType == null) {
            die('must not be executed');
        }

        if($user instanceof User){
            $log = $logRepository->findOneBy(
                    array(
                        'resourceType' => $resourceType->getId(),
                        'doer' => $user->getId(),
                        'action' => LogChapterReadEvent::ACTION,
                    ),
                    array('dateLog' => 'DESC')
                );
        } else {
            $log = null;
        }

        $firstChapter = null;
        if($log == null){
            $allChapters = $chapterRepository->findByLesson(array('lesson' => $lesson), array('left' => 'ASC'));
            foreach($allChapters as $chapter){
                if($chapter->getLevel() > 1){
                    if($firstChapter == null){
                        $firstChapter = $chapter;
                        break;
                    }
                }
            }
            if($firstChapter != null){
                $url = $router->generate('icap_lesson_chapter', array('resourceId' => $lesson->getId(), 'chapterId' => $firstChapter->getId()));
            } else {
                $url = $router->generate('icap_lesson', array('resourceId' => $lesson->getId()));
            }
        } else {
            $details = $log->getDetails();
            $url = $router->generate('icap_lesson_chapter', array('resourceId' => $details['chapter']['lesson'], 'chapterId' => $details['chapter']['chapter']));
        }
        
        return $url;
    }

}