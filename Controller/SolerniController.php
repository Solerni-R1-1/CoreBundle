<?php

/*
 * To change this license header, choose License Headers in Project Properties. To change this template file, choose Tools | Templates and open the template in the editor.
 */
namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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


/**
 * Description of SolerniController
 *
 * @author Simon Vart <svart@sii.fr>
 * @author Anas AMEZIANE <aameziane@sii.fr>
 *
 * @copyright 2014 @ sii.fr for Orange
 *
 */
class SolerniController extends Controller
{

    
      
    /**
     * Get and separate all badges in two categories depending if they are 
     * associated to dropzones or exercices
     * 
     * @param AbstractWorkspace workspace
     * @param User user
     */
    public function getWorkspaceUserBadges(AbstractWorkspace $workspace, User $user) {
        $doctrine = $this->getDoctrine();
        $badges = $doctrine->getRepository('ClarolineCoreBundle:Badge\Badge')->findByWorkspace($workspace);

        $locale = $this->getRequest()->getLocale();

        $knowledgeBadges = array();
        $myKnowledgeBadges = array();
        $skillBadges = array();
        $mySkillBadges = array();
        $userId = ($user != null ? $user->getId() : null);

        foreach ($badges as $badge) {
            $translation = $badge->getTranslationForLocale($locale);
            $badgeValue = array(
                'id' => $badge->getId(),
                'slug' => $badge->getSlug(),
                'name' => $translation->getName()
            );

            foreach($badge->getRules() as $rule){
                if (strpos($rule->getAction(), 'ujm_exercise') !== false) {
                $knowledgeBadges[] = $badgeValue;
                if ($badge->getUsers()->contains($user)) {
                    $myKnowledgeBadges[] = $badgeValue['id'];
                }
                    break;
                }
                if (strpos($rule->getAction(), 'icap_dropzone') !== false) {
                $skillBadges[] = $badgeValue;
                if ($badge->getUsers()->contains($user)) {
                    $mySkillBadges[] = $badgeValue['id'];
                }
                    break;
                }
            }
        }

        return array(
            'BadgesKnowledge' => array(
                'UserBadgesCollection' => $myKnowledgeBadges,
                'BadgesCollection'      => $knowledgeBadges,
            ),
            'BadgesSkills' => array(
                'UserBadgesCollection' => $mySkillBadges,
                'BadgesCollection'      => $skillBadges,
            ),
            'workspace' => $workspace
        );


    }
    
    /**
     * 
     * Render widget in lesson bundle for the Apprendre Tab
     * 
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"id" = "workspaceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function getWorkspaceUserBadgesWidgetAction($workspace, $user)
    {
        return $this->render(
                'ClarolineCoreBundle:Partials:workspaceUserBadgesWidget.html.twig',
                $this->getWorkspaceUserBadges(
                    $workspace,
                    $user
                )
        ); 
    }
    
    /*
     * get static page url from parameters.yml (Ooh that's evil)
     */
    private function getStaticPage($name){

        // check values into parameters.yml. Also take a look inside README.md for example
        // it's must be something like "solerni_static_$word"
        if($this->container->hasParameter('solerni_' . $name)) {
            return $this->container->getParameter('solerni_' . $name);
        } else {
            throw $this->createNotFoundException('Cette URL statique n\'est pas configurée');
        }

     }

    /**
     * 
     * Renders the profil widget on dashboard and any other page that could use it 
     * (the rendering context is used just to activate the right item in the menu for current page)
     * 
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function getDesktopAsideBlockWidgetAction(User $user, $renderingContext = null )
    {
        $router = $this->get('router');
        $static = $this->get('orange.static.controller');
        $thumbnail = $this->get('claroline.utilities.thumbnail_creator');
        $picDft = 'avatar.jpg';

        // Check for user picture or generate default avatar
        $pathDft = realpath($this->container->getParameter('claroline.param.thumbnails_directory').'/../bundles/clarolinecore/images/'.$picDft);
        $path = realpath($this->container->getParameter('claroline.param.thumbnails_directory').'/../uploads/pictures/' . $user->getPicture()) ;
        $pathDest = realpath($this->container->getParameter('claroline.param.thumbnails_directory')) . DIRECTORY_SEPARATOR . 'tmb_54_54_' . $user->getPicture();
        $pathDftDest = realpath($this->container->getParameter('claroline.param.thumbnails_directory')) . DIRECTORY_SEPARATOR . 'tmb_54_54_' . $picDft;

        $thb = null;
        if(file_exists($pathDest)){
            $thb = 'tmb_54_54_' .  $user->getPicture();
        } else if(!file_exists($pathDest) && file_exists($path) && $user->getPicture() != ""){
            $thumbnail->fromImage($path, $pathDest, 54, 54);
            $thb = 'tmb_54_54_' .  $user->getPicture();
        } else if(file_exists($pathDft)) {
            $thumbnail->fromImage($pathDft, $pathDftDest, 54, 54);
            $thb = 'tmb_54_54_' . $picDft;
        }

    	return $this->render(
    			'ClarolineCoreBundle:Partials:desktopAsideBlockWidget.html.twig',
    			array(
                    'user'  => $user,
                    'userThumbnailSrc' => $thb,
                    'nbMessages' => $this->getDoctrine()
                        ->getRepository('ClarolineCoreBundle:Message')
                        ->countUnread($user),
                    'renderingContext' => $renderingContext
                )
    	);
    }

    /**
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function getDesktopLessonBlockWidgetAction(User $user)
    {
        $doctrine = $this->getDoctrine();
        //Get the static pages controller
        $static = $this->get('orange.static.controller');
        $workspaceRepository = $doctrine->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $chapterRepository = $doctrine->getRepository('IcapLessonBundle:Chapter');
        $doneRepository = $doctrine->getRepository('IcapLessonBundle:Done');
        $roleRepository = $doctrine->getRepository('ClarolineCoreBundle:Role');

        $allWorkspaces = $workspaceRepository->findNonPersonal();
        $router = $this->get('router');

        $return =  array();
        $return['lessons'] = array();
        foreach($allWorkspaces as $workspace){
            $lesson = $this->getFirstLessonFromWorkspace($workspace);
            if($lesson != null){
                $allChapters = $chapterRepository->findByLesson(array('lesson' => $lesson));
                $image = $this->getSecondImageFromWorkspace($workspace, 'image/.*');

                if($image != null){
                    list ($imageWidth, $imageHeight) = getimagesize($this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR . $image->getHashName());
                    $lessonThumbnail = array(
                        'src' => $this->get('router')->generate('claro_image', array('node' => $image->getResourceNode()->getId())),
                        'alt' => $image->getName(),
                        'width' => $imageWidth,
                        'height' => $imageHeight
                    );
                } else {
                    $lessonThumbnail = null;
                }

                //Quick and dirty
                $totalProgression = 0;
                $currentProgression = 0;
                $allChapters = $chapterRepository->findByLesson(array('lesson' => $lesson), array('left' => 'ASC'));
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

                $is_registered = $this->isUserRegisteredinWorkspace( $user, $workspace );
                
                if($is_registered){
                    $url = $this->getRouteToTheLastChapter($lesson, $user);
                    $this->get('session')->set('user_registered_in_lesson', true);
                } else {
                    $url = $static->getStaticPage('static_lesson');
                }

                $return['lessons'][] =  array(
                    'isRegistered' => $is_registered,
                    'lessonTitleMain' => $lesson->getResourceNode()->getName(),
                    'lessonUrl' => $url,
                    'lessonMetaNbActivites' => count($allChapters),
                    'lessonProgression' => ($totalProgression == 0) ? null : round($currentProgression / $totalProgression * 100),
                    'lessonThumbnail' => $lessonThumbnail,

                    //TODO check if dynamic
                    'lessonTheme' => null,
                    'lessonTitleSub' => 'proposé par Orange',
                    'lessonDesc' => '10 semaines pour explorer, tester et
            						débattre des innovations techniques qui
            						bouleversent nos activités quotidiennes...',
                    'lessonMetaDate' => 'Début le 31/03/14 pour 10 semaines',
                    'lessonMetaBadges' => 'Badgeant',
                    'lessonMetaPrice' => 'Mooc gratuit',
                );
            }
        }

    	return $this->render(
            'ClarolineCoreBundle:Partials:desktopLessonBlockWidget.html.twig',
            $return
    	);
    }

    /**
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function getDesktopMessagesBadgesAndEvalBlockWidgetAction(User $user)
    {
        
        $doctrine = $this->getDoctrine();
        $router = $this->get('router');
        $translator = $this->get('translator');
        //Get the static pages controller
        $static = $this->get('orange.static.controller');

        $messageReturn = array(
            'messagesBoxUrl' => $router->generate('claro_message_list_received'),
            'lastMessage' => null,
        );
        $userMessages = $doctrine->getRepository('ClarolineCoreBundle:UserMessage')->findReceived($user);
        if(count($userMessages) > 0){
            $message = $userMessages[0]->getMessage();
            $messageContent = strip_tags($message->getContent());
            $messageReturn['lastMessage'] = array(
                'title' => $message->getObject(),
                'time' => $message->getDate()->format('H\hi'),
                'summary' => $messageContent,
                'url' => $router->generate('claro_message_show', array('message' => $message->getId()))
            );
        }

        $badgeReturn = array(
            'badgesListPageUrl' => $router->generate('claro_profile_view_badges'),
            'lastBadge' => null,
        );
        
        $userBadges = $user->getUserBadges();
        if(count($userBadges) > 0){
            $lastBadge = null;
            foreach($userBadges as $badge){
                if($lastBadge == null){
                    $lastBadge = $badge;
                } elseif($lastBadge->getIssuedAt() < $badge->getIssuedAt()){
                    $lastBadge = $badge;
                }
            }

            /* remove Soft Deletable Filter in case we have modified the badge after it was acquired */
            $doctrine->getEntityManager()->getFilters()->disable('softdeleteable');
            $lastBadge = $lastBadge->getBadge();
            $badgeReturn['lastBadge'] = array(
                'title' => $lastBadge->getName(),
                'summary' =>  $lastBadge->getDescription(),
                'obj'   => $lastBadge,
                //'type' => $translator->trans('knowledge_badges', array(), 'platform'),
                'url' => $router->generate('claro_view_badge', array('slug' => $lastBadge->getSlug()))
            );
            
        }

        $evals = $doctrine->getRepository('IcapDropzoneBundle:Drop')->findByUser($user);

        $lastEval = null;
        foreach ($evals as $eval) {
            if($lastEval == null || $eval->getDropDate() > $lastEval->getDropDate()) {
                $lastEval = $eval;
            }
        }
        //var_dump($lastEval);
        if ($lastEval == null) {
            $evalReturn = array (
                'evalsPageUrl' => $static->getStaticPage('static_eval'),
                'lastEval' => null
            );
        } else {
            $evalReturn = array (
                'evalsPageUrl' => $static->getStaticPage('static_eval'),
                'lastEval' => array(
                    'title' => $lastEval->getDropzone()->getResourceNode()->getName(),
                    'url' => $router->generate(
                            'icap_dropzone_drop',
                            array('resourceId' =>$lastEval->getDropzone()->getId())),
                    'summary' => strip_tags($lastEval->getDropzone()->getInstruction())
                ),
            );
        }
        

        return $this->render(
            'ClarolineCoreBundle:Partials:desktopMessagesBadgesAndEvalBlockWidget.html.twig',
            $messageReturn + $badgeReturn + $evalReturn
        );
    }

    /**
     *
     *
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"id" = "workspaceId"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getWorkspaceUserLessonsWidgetAction($workspace)
    {
        $doctrine = $this->getDoctrine();
        $resourcesTypeRepository = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $resourcesNodeRepository = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $user = $this->get('security.context')
            ->getToken()
            ->getUser();
        $lessonRepository = $doctrine->getRepository('IcapLessonBundle:Lesson');
        $chapterRepository = $doctrine->getRepository('IcapLessonBundle:Chapter');
        $doneRepository = $doctrine->getRepository('IcapLessonBundle:Done');

        // TODO see if it can be parametered
        $resourceType = $resourcesTypeRepository->findOneByName('icap_lesson');
        if ($resourceType == null) {
            // TODO manage error
            die('must not be executed');
        }

        $all = $resourcesNodeRepository->findByWorkspaceAndResourceType($workspace, $resourceType);
        $convertedTab = array(
            'lessons' => array()
        );

        foreach ($all as $lessonNode) {
            $lesson = $lessonRepository->findOneByResourceNode($lessonNode);
            $currentLesson = array(
                'id' => $lesson->getId(),
                'title' => $lessonNode->getName(),
                'chapters' => array()
            );

            $root = $lesson->getRoot();

            if ($root != null) {
                $chapters = $chapterRepository->getChapterTree($root);
                if ((count($chapters) == 1) && $chapters[0]['id'] == $root->getId()) {
                    // Remove root if present.
                    $chapters = $chapters[0]['__children'];
                }

                // Convert Table without recursive system
                $nodeToTreat = array();
                $parents = array();
                $currentTreatment = 0;
                foreach ($chapters as &$chapter) {
                    $nodeToTreat[] = $chapter;
                    $parents[] = &$currentLesson['chapters'];
                    unset($chapter);
                }

                while (isset($nodeToTreat[$currentTreatment])) {
                    $done = (is_object($user) && $user instanceof User) ?
                            $doneRepository
                            ->find(array(
                        'lesson' => $nodeToTreat[$currentTreatment]['id'],
                        'user' => $user->getId()
                    )) : false;
                    if ($done == null) {
                        $done = false;
                    } else {
                        $done = $done->getDone();
                    }
                    $nodeValue = array(
                        'id' => $nodeToTreat[$currentTreatment]['id'],
                        'title' => $nodeToTreat[$currentTreatment]['title'],
                        'slug' => $nodeToTreat[$currentTreatment]['slug'],
                        'done' => $done,
                        'childs' => array()
                    );
                    foreach ($nodeToTreat[$currentTreatment]['__children'] as $child) {
                        $nodeToTreat[] = &$child;
                        $parents[] = &$nodeValue['childs'];
                        // Free pointer, avoiding overrides
                        unset($child);
                    }

                    $parents[$currentTreatment][] = $nodeValue;
                    $currentTreatment ++;
                    // Free pointer
                    unset($nodeValue);
                }
            }
            $convertedTab['lessons'][] = $currentLesson;
            unset($currentLesson);
        }

        return $this->render(
            'ClarolineCoreBundle:Partials:workspaceUserLessonsWidget.html.twig',
            $convertedTab
        );
    }

    public function getDesktopProfileWidgetAction()
    {
        $user = $this->get('security.context')
            ->getToken()
            ->getUser();
        $userWorkspaces = $this->getDoctrine()
            ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findByUser($user);
        $return = array(
            'workspaces' => $userWorkspaces
        );

        return $this->render(
                'ClarolineCoreBundle:Partials:desktopProfileWidget.html.twig'
        );
    }

    /**
     * Return the first element of the required type
     *
     * @param AbstractWorkspace $workspace
     * @param string $resourceName the name of the resource
     * @param string $mimeType (optionnal) the mime type. Accept wildcards.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    private function getFirstResourceFromWorkspace(AbstractWorkspace $workspace, $resourceName, $mimeType = null)
    {
        $doctrine = $this->getDoctrine();
        $resourcesNodeRepository = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceNode');

        $rootResource = $resourcesNodeRepository->findWorkspaceRoot($workspace);
        $current = $resourcesNodeRepository->findOneBy(array(
            'parent' => $rootResource,
            'previous' => null
        ));
        while (
            $current !== null
            && (
                $current->getResourceType()->getName() != $resourceName
                || (
                     ($mimeType !== null)
                     && (!preg_match('#'.$mimeType.'#', $current->getMimeType())
                   )
               )
            )
        ) {
            $current = $current->getNext();
        }

        return $current;
    }

    /**
     * Return all elements of the required type
     *
     * @param AbstractWorkspace $workspace
     * @param string $resourceName the name of th resource
     * @param string $mimeType (optionnal) the mime type. Accept wildcards.
     * @param int number, max number of result
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    private function getXResourcesFromWorkspace(AbstractWorkspace $workspace, $resourceName, $mimeType = null, $number = 10)
    {
        $doctrine = $this->getDoctrine();
        $resourcesNodeRepository = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceNode');

        $rootResource = $resourcesNodeRepository->findWorkspaceRoot($workspace);
        $current = $resourcesNodeRepository->findOneBy(array(
            'parent' => $rootResource,
            'previous' => null
        ));

        if($mimeType == null){
            return array();
        }

        $results = array();
        while ($current !== null){
            if (preg_match('#'.$mimeType.'#', $current->getMimeType())){
                $results[] = $current;
                if(count($current) >= $number){
                    break;
                }
            }
            $current = $current->getNext();
        }
        return $results;
    }

}
