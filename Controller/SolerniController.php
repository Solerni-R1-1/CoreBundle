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
     * associated to dropzones or exercices. Display in lesson (onglet Apprendre)
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
     * Display a footer block on the desktop. Called from the desktop Twig 
     * 
     *
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function getDesktopFooterBlockMessageAction(
            $user,
            $target,
            $mainTitle =            "Title",
            $containerClass =       "",
            $statusText =           "Status",
            $iconClass =            "no_class",
            $iconImageSubstitute =  "",
            $subTitle =             "",
            $subText =              "Sub Text",
            $subUrl =               "#",
            $footerUrl =            "#",
            $footerText =           "Footer Text"
    ) {
        
        $translator = $this->get('translator');
        $doctrine = $this->getDoctrine();
        $router = $this->get('router');

        switch ( $target ) {
            
            case 'message':
                $userMessages = $doctrine->getRepository('ClarolineCoreBundle:UserMessage')->findReceived($user);
                if ( count($userMessages ) > 0 ) {
                    $message = $userMessages[0]->getMessage();
                    $iconClass = $iconClass . '_actif';
                    $statusText = $translator->trans( 'last_message', array(), 'platform');
                    $messageTitle = $message->getObject();
                    $subTitle = ( mb_strlen ( $messageTitle, "UTF-8" ) > 15 ) ? mb_substr( $messageTitle, 0, 15 , "UTF-8") . '...' : $messageTitle;
                    $subTitle .= ' ' . $translator->trans( '@at', array(), 'platform') . ' ' .  $message->getDate()->format('H\hi');
                    $messageContent = strip_tags( $message->getContent() );
                    $subText = ( mb_strlen ( $messageContent, "UTF-8" ) > 30 ) ? mb_substr( $messageContent, 0, 30, "UTF-8" ) . '...' : $messageContent;
                    $subUrl = $router->generate('claro_message_show', array('message' => $message->getId()));
                }
                break;
            
            case 'badges':
                $userBadges = $user->getUserBadges();
                if( count( $userBadges ) > 0 ) {
                    $lastBadge = null;
                    foreach($userBadges as $badge){
                        if($lastBadge == null){
                            $lastBadge = $badge;
                        } elseif($lastBadge->getIssuedAt() < $badge->getIssuedAt()){
                            $lastBadge = $badge;
                        }
                    }
                    /* Special case : remove Soft Deletable Filter in case we have modified the badge after it was acquired */
                    $doctrine->getManager()->getFilters()->disable('softdeleteable');
                    $lastBadge = $lastBadge->getBadge();
                    $containerClass = 'footer__block__withImage';
                    $iconClass = $iconClass . '_actif';
                    $iconImageSubstitute = $lastBadge->getWebPath();
                    $statusText = $translator->trans( 'last_badge', array(), 'platform');
                    $badgeTitle = $lastBadge->getName();
                    $subTitle = ( mb_strlen ( $badgeTitle, "HTML-ENTITIES" ) > 20 ) ? mb_substr( $badgeTitle, 0, 15, "HTML-ENTITIES"  ) . '...' : $badgeTitle;
                    if ( $lastBadge->getWorkspace()->getMooc() ) {
                        $subText = $lastBadge->getWorkspace()->getMooc()->getTitle();
                    } else {
                        $subText = $lastBadge->getWorkspace()->getName();
                    }
                    $subUrl = $router->generate('claro_view_badge', array('slug' => $lastBadge->getSlug()));
                    // Renable Soft Deletable Filter
                    $doctrine->getManager()->getFilters()->enable('softdeleteable');
                }
                break;
            
            case 'evals':
            	// Services initialization
            	$badgeManager = $this->get('claroline.manager.badge');

            	// Get all in progress badges
            	$badgesInProgress = $badgeManager->getAllBadgesInProgress($user);
            	
            	// Change status to reflect the number of ongoing badges
            	$evalsCount = 0;
            	foreach ($badgesInProgress as $badgeWorkspace) {
            		foreach ($badgeWorkspace['badges'] as $badge) {
            			$evalsCount++;
            		}
            	}
            	// If we have at least a badge
            	if ($evalsCount > 0) {
            		// Repositories init
	            	$logRepository = $doctrine->getRepository('ClarolineCoreBundle:Log\Log');
	            	$resourceType = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('icap_dropzone');
	            	
	            	// Pluralize
            		$plural = $evalsCount > 1 ? 's' : '';
	            	$statusText = $translator->trans( 'in_progress_badges', array( '%number%' => $evalsCount, '%plural%' => $plural ), 'platform');
	            	
	            	$mostRecentAction = null;
	            	$lastBadge;
	            	// Loop on all the badges of all workspaces
	            	foreach($badgesInProgress as $badgeWorkspace) {
	            		foreach($badgeWorkspace['badges'] as $badge) {
	            			// Get the last action made by the user on the associated dropzone
	            			$lastDoerAction = $logRepository->findOneBy(
	            					array(
	            							'resourceType' => $resourceType,
	            							'doer' => $user,
	            							'resourceNode' => $badge['resource']['resource']['dropzone']->getResourceNode()
	            					),
	            					array('dateLog' => 'DESC')
	            			);
	            			
	            			// If we have no most recent action yet or if the last found action is posterior to the one saved
	            			if ($mostRecentAction == null
	            					|| $lastDoerAction->getDateLog() > $mostRecentAction->getDateLog()) {
	            				// Replace it and save the last badge
	            				$mostRecentAction = $lastDoerAction;
	            				$lastBadge = $badge;
	            			}
	            		}
	            	}
	
	            	//show last eval
	            	$containerClass = 'footer__block__withImage';
	            	$iconClass .= '_actif';
	            	$iconImageSubstitute = $lastBadge['badge']->getWebPath();
	            	$subTitle = $lastBadge['badge']->getName();
	            	$subText = $lastBadge['badge']->getWorkspace()->getMooc()->getTitle();
	            	$subUrl = $lastBadge['resource']['url'];
	            	$footerText = $translator->trans( 'show_my_evals', array(), 'platform');
	            	$footerUrl = $router->generate('solerni_user_evaluations');
            	}
                break;
            }
        
        return $this->render(
            'ClarolineCoreBundle:Partials:desktopFooterBlock.html.twig',
            array(
                'containerClass'        => $containerClass,
                'mainTitle'             => $mainTitle,
                'statusText'            => $statusText,
                'iconClass'             => $iconClass,
                'iconImageSubstitute'   => $iconImageSubstitute,
                'subTitle'              => $subTitle,
                'subText'               => $subText,
                'subUrl'                => $subUrl,
                'footerUrl'             => $footerUrl,
                'footerText'            => $footerText
            )
        );  
    }
    

    /**
     * Display the widget with the list of the chapters inside a lesson in a mooc (chemin de fer)
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
