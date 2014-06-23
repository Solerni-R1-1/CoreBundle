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

class BadgeController extends Controller
{
    public function myWorkspaceBadgeAction(AbstractWorkspace $workspace, User $loggedUser, $badgePage, $resourceType = 'all', $resourceId = null, $needEcho = true )
    {
        /** @var \Claroline\CoreBundle\Rule\Validator $badgeRuleValidator */
        $badgeRuleValidator = $this->get("claroline.rule.validator");

        /** @var \Claroline\CoreBundle\Entity\Badge\Badge[] $workspaceBadges */
        $workspaceBadges = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Badge\Badge')->findByWorkspace($workspace);

        $ownedBadges      = array();
        $inProgressBadges = array();
        $availableBadges  = array();
        $displayedBadges  = array();
        $nbTotalBadges    = 0;
        $nbAcquiredBadges = 0;

        foreach ($workspaceBadges as $workspaceBadge) {
            
            if ( $resourceType == null ) {
                $resourceType = 'all';
            }
            
            /* filter badges from name and resource ID to check rules associated with the badge */
            if ( $resourceType != 'all' && $resourceId != null ) {
                if ( ! $this->isOneRuleAssociatedWithResourceId( $workspaceBadge, $resourceId ) ) {
                   continue;
                }
            } elseif ( $resourceType != 'all' ) {
                if ( ! $this->isOneRuleAssociatedWithResource( $workspaceBadge, $resourceType ) ) {
                   continue;
                }
            }
            
            $isOwned = false;
            foreach ($workspaceBadge->getUserBadges() as $userBadge) {
                if ($loggedUser->getId() === $userBadge->getUser()->getId()) {
                    $ownedBadges[] = $userBadge;
                    $isOwned = true;
                    $nbAcquiredBadges++;
                    $nbTotalBadges++;
                }
            }

            if (!$isOwned) {
                $nbBadgeRules      = count($workspaceBadge->getRules());
                $validatedRules    = $badgeRuleValidator->validate($workspaceBadge, $loggedUser);

                if(0 < $nbBadgeRules && 0 < $validatedRules['validRules'] && $nbBadgeRules >= $validatedRules['validRules']) {
                    $inProgressBadges[] = $workspaceBadge;
                    $nbTotalBadges++;
                }
                else {
                    $availableBadges[] = $workspaceBadge;
                    $nbTotalBadges++;
                }
            }
        }

        // Create badge list to display (owned badges first, in progress badges and then other badges)
        $displayedBadges = array();
        foreach ($ownedBadges as $ownedBadge) {
            $displayedBadges[] = array(
                'type'  => 'owned',
                'badge' => $ownedBadge,
                'associatedResourceUrl'  => $this->getResourceUrlAssociatedWithRule( $ownedBadge->getBadge(), $resourceType ),
                'associatedResource' => $this->getResourceAssociatedWithBadge( $ownedBadge->getBadge(), $resourceType, $loggedUser )
            );
        }

        foreach ($inProgressBadges as $inProgressBadge) {
            $displayedBadges[] = array(
                'type'          => 'inprogress',
                'badge'         => $inProgressBadge,
                'associatedResourceUrl'  => $this->getResourceUrlAssociatedWithRule( $inProgressBadge, $resourceType ),
                'associatedResource' => $this->getResourceAssociatedWithBadge( $inProgressBadge, $resourceType, $loggedUser )
            );
        }

        foreach ($availableBadges as $availableBadge) {
            $displayedBadges[] = array(
                'type'  => 'available',
                'badge' => $availableBadge,
                'associatedResourceUrl'  => $this->getResourceUrlAssociatedWithRule( $availableBadge, $resourceType ),
                'associatedResource' => $this->getResourceAssociatedWithBadge( $availableBadge, $resourceType, $loggedUser )
            );
        }

        /** @var \Claroline\CoreBundle\Pager\PagerFactory $pagerFactory */
        $pagerFactory = $this->get('claroline.pager.pager_factory');
        $badgePager   = $pagerFactory->createPagerFromArray($displayedBadges, $badgePage, 10);

        $badgeList = array(
            'badgePager'        => $badgePager,
            'workspace'         => $workspace,
            'badgePage'         => $badgePage,
            'nbTotalBadges'     => $nbTotalBadges,
            'nbAcquiredBadges'  => $nbAcquiredBadges
        );
        
        /* if we need this data from another controller */
        if ( $needEcho == false ) {
            return $badgeList;
        }

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
    public function getResourceAssociatedWithBadge( $badge, $resourceType, $loggedUser ) {
        
        if ( strpos( $resourceType, 'dropzone' ) ) {
            $associatedResource = array();
            $doctrine = $this->getDoctrine();
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
        }
        
        return $associatedResource;
    }
}
