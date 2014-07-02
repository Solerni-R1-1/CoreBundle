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
//use Icap\LessonBundle\Repository\LessonRepository;
use Icap\LessonBundle\Entity\Lesson;
use Claroline\CoreBundle\Controller\SolerniController;

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

        /**
         * @Route("/{moocid}/{moocname}/sessions", name="mooc_view")
         */
        public function moocPageAction($moocid, $moocname){
            //Check pattern
            if(!preg_match($this->patternId, $moocid)){
            	return $this->inner404("parametre moocid invalid : ".$moocid);
            }

            //check the mooc
            $mooc = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Mooc\Mooc')
            		->find($moocid);

			if($mooc == null){
            	return $this->inner404("le mooc n'existe pas : ".$moocid);
            }

            $sessions = $mooc->getMoocSessions();

            $session = null;
            if(!empty($sessions)) {
                $session = $sessions[0];
            }

            return $this->render(
                'ClarolineCoreBundle:Mooc:mooc.html.twig',
                array(
                    'mooc'     => $mooc,
                    'session'  => $session
                )
            );
        }
        
        /**
         * @Route("/{moocid}/{moocname}/session/{sessionid}/{word}", name="mooc_view_session")
         * @ParamConverter("user", options={"authenticatedUser" = true})
         */
        public function sessionPageAction($moocid, $moocname, $sessionid, $word, $user){


        	if(!preg_match($this->patternId, $moocid)){
            	return $this->inner404("parametre moocid invalid : ".$moocid);
            }

            if(!preg_match($this->patternId, $sessionid)){
            	return $this->inner404("parametre sessionid invalid : ".$sessionid);
            }

            //check the mooc
            $session = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Mooc\MoocSession')->find($sessionid);
            if($session == null){
            	return $this->inner404("la session n'existe pas : ".$sessionid);
            }
            if($session->getMooc()->getId() != $moocid){
                return $this->inner404("mooc non correspondant . expected : "
                            .$session->getMooc()->getId()." given : ".$moocid);
            }


        	switch ($word){
        		case "apprendre" : 
	        		return $this->sessionApprendrePage($session->getMooc(), $user);
	        		break;

        		case "discuter" : 
	        		return $this->sessionDiscuterPage($session);
	        		break;

        		case "partager" :
	        		return $this->sessionPartagerPage($session->getMooc()->getWorkspace());
	        		break;

        		case "vomir" :
        		case "subir" :
	        		return $this->sessionLolPage($session);
	        		break;

        		default:return $this->inner404("le word est inconnu : ".$word);
        	}

        }

        private function inner404($warn){
            return $this->render(
                'ClarolineCoreBundle:Mooc:mooc_error.html.twig',
                array(
                    'warn'     => $warn
                )
            );
        }

        private function sessionApprendrePage($mooc, $user) {

            $node = $mooc->getLesson();

            $repo = $this->getDoctrine()->getRepository('IcapLessonBundle:Lesson');
            $lesson = $repo->findOneByResourceNode($node);

            $url = $this->getRouteToTheLastChapter($lesson, $user);
            return  $this->redirect($url);

/*
            if($node != null){
                $resourceType = $node->getResourceType()->getName();
                $nodeid = $node->getId();
                $url = $this->get('router')
                             ->generate('claro_resource_open', array('resourceType' => $resourceType, "node" => $nodeid));
                return  $this->redirect($url);
            }
*/
            // 404
            //$warn = "Aucune leçon n'est associée à ce mooc";
            //return $this->inner404($warn);
        }
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
        private function sessionPartagerPage($workspace) {

            $workspaceId = $workspace->getId();

            $url = $this->get('router')
                         ->generate('claro_workspace_open_tool', array('workspaceId' => $workspaceId, "toolName" => "resource_manager"));
            return  $this->redirect($url);
            
        }
        private function sessionLolPage($session) {

            //TODO : make inner redirection instead JS redirect
            return $this->render(
                'ClarolineCoreBundle:Mooc:redirect_tmp.html.twig',
                array(
                    'session'     => $session,
                    'redirection'     => 'Bon ben là ....'
                )
            );
        }

        /**
     * get the route to the last chapter read, according to the log.
     *
     * @param Lesson $lesson
     * @param User|string $user
     * @return string
     */
    private function getRouteToTheLastChapter(Lesson $lesson, $user){
        $router = $this->get('router');
        $doctrine = $this->get('doctrine');
        $logRepository = $doctrine->getRepository('ClarolineCoreBundle:Log\Log');
        $chapterRepository = $doctrine->getRepository('IcapLessonBundle:Chapter');
        // TODO see if it can be parametered
        $resourceType = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('icap_lesson');
        if ($resourceType == null) {
            // TODO manage error
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