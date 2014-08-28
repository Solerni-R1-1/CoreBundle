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

/**
 * Description of StaticController
 *
 * @author SII
 * 
 * @copyright 2014 @ sii.fr for Orange
 *           
 */

class MoocService extends Controller
{

    /**
     * Get the active (or next) session from a workspace
     * Return MoocSession Entity or null
     */
    public function getActiveSessionFromWorkspace( $workspace, $user ) {
        
	    return $this->getDoctrine()
        	->getRepository( 'ClarolineCoreBundle:Mooc\\MoocSession' )
      		->guessActiveMoocSession( $workspace, $user );
    }
    
    /**
     * Get the session from a workspace
     * Return MoocSession Entity or null
     */
    public function getSessionFromWorkspace($workspace, $user) {
	    $moocSession = $this->getDoctrine()
        	->getRepository( 'ClarolineCoreBundle:Mooc\\MoocSession' )
      		->guessMoocSession($workspace, $user);
    	
    	return $moocSession;
    }
    
    /*
     * Return user progression in lesson from workspace
     */
    public function getUserProgressionInLesson( $user, $workspace ) {
        
        $doctrine = $this->getDoctrine();
        $chapterRepository = $doctrine->getRepository('IcapLessonBundle:Chapter');
        $doneRepository = $doctrine->getRepository('IcapLessonBundle:Done');
        $lesson = $this->getLessonFromWorkspace($workspace, $user);
        
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

        return ($totalProgression == 0) ? 0 : round($currentProgression / $totalProgression * 100);
        
    }
    
    /**
     * get the route to the last chapter read from a lesson, according to the log.
     *
     * @param Lesson $lesson
     * @param User|string $user
     * @return string
     */
    public function getRouteToTheLastChapter( \Icap\LessonBundle\Entity\Lesson $lesson, $user )
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
    
    /**
     * @param Lesson $lesson
     *
     * @return \Icap\LessonBundle\Entity\Chapter
     */
    public function getFirstSubChapter( \Icap\LessonBundle\Entity\Lesson $lesson )
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
     * Get the lesson from a workspace
     * Return a Lesson Entity or Null
     */
    public function getLessonFromWorkspace($workspace, $user) {
        
        $doctrine = $this->getDoctrine();
        $lessonRepository = $this->getDoctrine()->getRepository('IcapLessonBundle:Lesson');
        $lesson = null;
        $session = $this->getSessionFromWorkspace($workspace, $user);
        
        if ( $session ) {
            $lessonNode = $session->getMooc()->getLesson();
            $lesson = $lessonRepository->findOneByResourceNode($lessonNode);
        } 
        
        return $lesson;
    }
    
    
    /**
     * 
     * Return the URL of the lesson associated to active lesson
     * 
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"id" = "workspaceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function getBackMoocUrl( $workspace, $user ) {
        return $this->getRouteToTheLastChapter( $this->getLessonFromWorkspace( $workspace, $user ), $user );
    }
    
}