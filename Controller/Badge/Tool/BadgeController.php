<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Badge\Tool;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Icap\DropzoneBundle\Entity\Dropzone;
use Claroline\CoreBundle\Entity\Badge\UserBadge;

class BadgeController extends Controller
{
    /*
     * Returns or render (depending of needEcho) lists of badges related to resource_types
     */
    public function myWorkspaceBadgeAction(
            AbstractWorkspace $workspace,
            User $loggedUser) {        
    	$badges = $this->getAllBadgesForWorkspace($loggedUser, $workspace);
    	
    	$nbTotalBadges = count($badges);
    	$nbAcquiredBadges = 0;
    	$nbFailedBadges = 0;
    	
    	foreach ($badges as $badge) {
    		if ($badge['status'] == Badge::BADGE_STATUS_OWNED) {
    			$nbAcquiredBadges++;
    		} else if ($badge['status'] == Badge::BADGE_STATUS_FAILED) {
    			$nbFailedBadges++;
    		}
    	}
    	
    	
        $badgeList = array(
        	'badges' => $badges,
        	'workspace' => $workspace,
        	'nbTotalBadges' => $nbTotalBadges,
        	'nbAcquiredBadges' => $nbAcquiredBadges,
        	'nbFailedBadges' => $nbFailedBadges
        );

        return $this->render(
            'ClarolineCoreBundle:Badge:Template/Tool/list.html.twig',
            $badgeList
        );
    }
    
    /*
     * @var $badge is instance of Claroline\CoreBundle\Entity\Badge\Badge
     * @var $resourceString is string part of resource type name in rules
     * 
     * @return bool 
     */
    public function isOneRuleAssociatedWithResource( $badge, $resourceType )
    {
        $returnBool = false;

        foreach ( $badgeRules = $badge->getRules() as $BadgeRule ) {
            if ( strpos( $BadgeRule->getAction(), $resourceType ) ) {
                $returnBool = true;
            }
        }

        return $returnBool;
    }
    
     /*
     * @var $badge is instance of Claroline\CoreBundle\Entity\Badge\Badge
     * @var $resourceId is int of the resource ID
     * 
     * @return bool
     */
    public function isOneRuleAssociatedWithResourceId( $badge, $resourceId ) {
        
        $returnBool = false;

        foreach ( $badgeRules = $badge->getRules() as $BadgeRule ) {
             $badgeResource = $BadgeRule->getResource();
             if ( $badgeResource ) {
                 if ( $badgeResource->getId() == $resourceId ) {
                     $returnBool = true;
                 }
            }
        }

        return $returnBool;
    }
    
     /*
     * Returns a url for the resource
     * if the badge has a rule attached to a specific resource
     * 
     * @var $badge is instance of Claroline\CoreBundle\Entity\Badge\Badge
     * @var $resourceString is string part of resource type in rules
     * 
     * @return string
     */
    public function getResourceUrlAssociatedWithRule( $badge, $resourceType ) {
        
        $returnUrl = null;
        
        foreach ( $badgeRules = $badge->getRules() as $BadgeRule ) {
            $badgeRuleResource = $BadgeRule->getResource();
            if ( strpos( $BadgeRule->getAction(), $resourceType ) && $badgeRuleResource ) {
                $returnUrl = $this->getUrlFromResourceNode( $badgeRuleResource );
            }
        }
              
        return $returnUrl;
    }
    /*
     * Return the url to open a resource
     * 
     * @var $resource is instance of Claroline\CoreBundle\Entity\Resource\ResourceNode 
     * 
     * @return string
     */
    public function getUrlFromResourceNode( $resource ) {
        
        $router = $this->get('router');
        return $router->generate('claro_resource_open', array(
                    'node' => $resource->getId(),
                    'resourceType' => $resource->getResourceType()->getName()
        ));       
    }
    
    /*
     *  @return instance of Claroline\CoreBundle\Entity\Resource\ResourceNode if resource ID found
     */
    public function getResourceAssociatedWithBadge(Badge $badge, $resourceType, $loggedUser ) {
    	$associatedResource = array();
        $doctrine = $this->getDoctrine();
        
        if ( strpos( $resourceType, 'dropzone' ) ) {
            $evalDropzoneRepo = $doctrine->getRepository('IcapDropzoneBundle:Dropzone');
            $evalDropRepo = $doctrine->getRepository('IcapDropzoneBundle:Drop');
            foreach ( $badgeRules = $badge->getRules() as $BadgeRule ) {
                if ( strpos( $BadgeRule->getAction(), $resourceType ) ) {
                    $badgeRessourceNode = $BadgeRule->getResource();
                    if ( $badgeRessourceNode ) {
                        $associatedDropzone = $evalDropzoneRepo->findOneByResourceNode( $badgeRessourceNode );
                        $associatedDrop = $evalDropRepo->findOneBy( array( 'dropzone' => $associatedDropzone, 'user' => $loggedUser ) );
                        $associatedResource = array( 'dropzone' => $associatedDropzone, 'drop' => $associatedDrop );
                    }
                }
            }
        } else if (strpos ($resourceType, 'exercise')) {
        	$exercisesRepo = $doctrine->getRepository('UJMExoBundle:Exercise');
        	foreach ( $badgeRules = $badge->getRules() as $BadgeRule ) {
        		if ( strpos( $BadgeRule->getAction(), $resourceType ) ) {
        			$badgeRessourceNode = $BadgeRule->getResource();
        			if ( $badgeRessourceNode ) {
        				$associatedExercise = $exercisesRepo->findOneByResourceNode( $badgeRessourceNode );
        				$associatedResource = array( 'exercise' => $associatedExercise );
        			}
        		}
        	}
        }
        return $associatedResource;
    }
    
    public function getAllBadgesForWorkspace(User $user, AbstractWorkspace $workspace, $knowledgeBadges = true, $skillBadges = false, $allBadges = false) {    	 
    	$badgeRepository = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Badge\Badge');

    	$badgesInProgress = array();
    	$workspaceBadges = $badgeRepository->findByWorkspace($workspace);
    	foreach($workspaceBadges as $badge) {
    		$knowledgeBadge = $badge->isKnowledgeBadge();
    		$skillBadge = $badge->isSkillBadge();
    		if ($allBadges
    				|| ($knowledgeBadges && $knowledgeBadge)
    				|| ($skillBadges && $skillBadge)) {
    			if ($knowledgeBadge) {
    				$resNode = $badge->getAssociatedEvaluations();
    			} else if ($skillBadge) {
    				$resNode = $badge->getAssociatedExercises();
    			} else {
    				// What to do in other situations ?
    				$resNode = null;
    			}
    			$badgeInProgress = array();
    			$badgeInProgress['badge'] = $badge;
    			if ($resNode != null) {
	    			$badgeInProgress['resource'] = array();
	    			$badgeInProgress['resource']['url'] = $this->getResourceUrlAssociatedWithRule( $badge, $resNode[0]->getResourceType()->getName() );
	    			$badgeInProgress['resource']['resource'] = $this->getResourceAssociatedWithBadge( $badge, $resNode[0]->getResourceType()->getName(), $user );
	    			$badgeInProgress['resource']['status'] = $badge->getBadgeResourceStatus($badgeInProgress['resource']['resource']);
    			}
    				
    			$status = $badge->getBadgeStatus($user, $badgeInProgress['resource']['status'], $this->get("claroline.rule.validator"));
    			$badgeInProgress['status'] = $status;
    			
    			if ($status == Badge::BADGE_STATUS_OWNED) {
    				foreach ($badge->getUserBadges() as $userBadge) {
    					if ($userBadge->getUser()->getId() == $user->getId()) {
    						$badgeInProgress['issuedDate'] = $userBadge->getIssuedAt();
    					}
    				}
    			}
    				
    			//if ($status == Badge::BADGE_STATUS_IN_PROGRESS) {
    				$badgesInProgress[] = $badgeInProgress;
    			//}
    		}
    	}
    	
    	return $badgesInProgress;
    }
    
    public function getAllBadgesInProgress(User $user) {
    	$WorkspacesBadgeList = array();
    	
    	$workspaces = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Workspace\AbstractWorkspace")->findAllWorkspacesUserIsRegisteredTo($user);
    	$badgeRepository = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Badge\Badge');
    	
    	$result = array();
    	
    	// get all badges associated to dropzone for each session subscribed
    	foreach($workspaces as $workspace) {
    		$badgesInProgress = array();
    		$workspaceBadges = $badgeRepository->findByWorkspace($workspace);
    		foreach($workspaceBadges as $badge) {
    	
    			if ($badge->isKnowledgeBadge()) {
    				$evalNode = $badge->getAssociatedEvaluations();
    				$badgeInProgress = array();
    				$badgeInProgress['badge'] = $badge;
    				$badgeInProgress['resource'] = array();
    				$badgeInProgress['resource']['url'] = $this->getResourceUrlAssociatedWithRule( $badge, $evalNode[0]->getResourceType()->getName() );
    				$badgeInProgress['resource']['resource'] = $this->getResourceAssociatedWithBadge( $badge, $evalNode[0]->getResourceType()->getName(), $user );
    				$badgeInProgress['resource']['status'] = $badge->getBadgeResourceStatus($badgeInProgress['resource']['resource']);
    				 
    				$status = $badge->getBadgeStatus($user, $badgeInProgress['resource']['status'], $this->get("claroline.rule.validator"));
    				$badgeInProgress['status'] = $status;
    				 
    				if ($status == Badge::BADGE_STATUS_IN_PROGRESS) {
    					$badgesInProgress[] = $badgeInProgress;
    				}
    			}
    		}
    		 
    		if (!empty($badgesInProgress)) {
    			$localResult = array();
    			$localResult['workspace'] = $workspace;
    			$localResult['badges'] = $badgesInProgress;
    			$result[] = $localResult;
    		}
    	}
    	
    	return $result;
    }
    
    /**
     * Display the page that aggregates all badges associated to a specific resource type
     * 
     * @Route("/mes_evaluations", name="solerni_user_evaluations")
     * @ParamConverter( "user", options={"authenticatedUser" = true })
     */
    public function userEvaluationsPageAction(User $user) {
        
        $result = $this->getAllBadgesInProgress($user);
        
        return $this->render(
            'ClarolineCoreBundle:Mooc:myEvaluationslist.html.twig',
            array( 'WorkspacesBadgeList' => $result )
        );
        
    }

}
