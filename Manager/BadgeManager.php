<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogBadgeAwardEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use UJM\ExoBundle\Services\classes\exerciseServices;

/**
 * @DI\Service("claroline.manager.badge")
 */
class BadgeManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;
    
    protected $router;
    
    protected $badgeValidator;
    
    /**
     * @var exerciseServices
     */
	protected $exerciseService;
    
    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "router"			 = @DI\Inject("router"),
     *     "badgeValidator"  = @DI\Inject("claroline.rule.validator"),
     *     "exerciseService" = @DI\Inject("ujm.exercise_services")
     * })
     */
    public function __construct(
    		EntityManager $entityManager,
    		EventDispatcherInterface $eventDispatcher,
    		$router,
    		$badgeValidator,
    		exerciseServices $exerciseService) {
        $this->entityManager   = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->router		   = $router;
        $this->badgeValidator  = $badgeValidator;
        $this->exerciseService = $exerciseService;
    }

    /**
     * @param Badge  $badge
     * @param User[] $users
     *
     * @return int
     */
    public function addBadgeToUsers(Badge $badge, $users)
    {
        $addedBadge = 0;

        foreach ($users as $user) {
            if ($this->addBadgeToUser($badge, $user)) {
                $addedBadge++;
            }
        }

        return $addedBadge;
    }

    /**
     * @param Badge $badge
     * @param User  $user
     *
     * @throws \Exception
     * @return bool
     */
    public function addBadgeToUser(Badge $badge, User $user)
    {
        $badgeAwarded = false;

        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository $badgeRepository */
        $badgeRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\Badge');
        $userBadge       = $badgeRepository->findUserBadge($badge, $user);

        if (null === $userBadge) {
            try {
                $userBadge = new UserBadge();
                $userBadge
                    ->setBadge($badge)
                    ->setUser($user);

                if ($badge->isExpiring()) {
                    $userBadge->setExpiredAt($this->generateExpireDate($badge));
                }

                $badge->addUserBadge($userBadge);

                $badgeAwarded = true;

                $this->entityManager->persist($badge);
                $this->entityManager->flush();

                $this->dispatchBadgeAwardingEvent($badge, $user);
            } catch(\Exception $exception) {
                throw $exception;
            }
        }

        return $badgeAwarded;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Badge\Badge $badge
     * @param \Claroline\CoreBundle\Entity\User        $user
     *
     * @return Controller
     */
    protected function dispatchBadgeAwardingEvent(Badge $badge, User $user)
    {
        $event = new LogBadgeAwardEvent($badge, $user);

        $this->dispatch($event);
    }

    /**
     * @param LogGenericEvent $event
     */
    protected function dispatch(LogGenericEvent $event)
    {
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * @param Badge          $badge
     * @param \DateTime|null $currentDate
     *
     * @return \DateTime
     */
    public function generateExpireDate(Badge $badge, \DateTime $currentDate = null)
    {
        if (null === $currentDate) {
            $currentDate = new \DateTime();
        }

        $modifier = sprintf("+%d %s", $badge->getExpireDuration(), $badge->getExpirePeriodTypeLabel($badge->getExpirePeriod()));
        return $currentDate->modify($modifier);
    }

    /**
     * @param BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $newRules
     * @param BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $originalRules
     *
     * @return bool
     */
    public function isRuleChanged($newRules, $originalRules)
    {
        $isRulesChanged = false;
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $unitOfWork->computeChangeSets();

        foreach ($newRules as $newRule) {
            // Check if there are new rules
            if (null === $newRule->getId()) {
                $isRulesChanged = true;
            }
            else {
                // Check if existed rules have been changed
                $changeSet = $unitOfWork->getEntityChangeSet($newRule);
                if (0 < count($changeSet)) {
                    $isRulesChanged = true;
                }
                // Remove rule from original if they were not deleted
                if ($originalRules->contains($newRule)) {
                    $originalRules->removeElement($newRule);
                }
            }
        }

        // Check if they are deleted rules (those who are not in the new but in the originals)
        if (0 < count($originalRules)) {
            $isRulesChanged = true;
        }

        return $isRulesChanged;
    }
    
    public function getAllBadgesForWorkspace(User $user, AbstractWorkspace $workspace, $knowledgeBadges = true, $skillBadges = false, $allBadges = false) {
    	$badgeRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\Badge');
    
    	$badgesInProgress = array();
    	$workspaceBadges = $badgeRepository->findByWorkspace($workspace);
    	foreach($workspaceBadges as $badge) {
    		$knowledgeBadge = $badge->isKnowledgeBadge();
    		$skillBadge = $badge->isSkillBadge();
    		if ($allBadges
    				|| ($knowledgeBadges && $knowledgeBadge)
    				|| ($skillBadges && $skillBadge)) {
    					if ($skillBadge) {
    						$resNode = $badge->getAssociatedEvaluations();
    					} else 
                        if ($knowledgeBadge) {
    						$resNode = $badge->getAssociatedExercises();
    					} else  {
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
    
    					$status = $badge->getBadgeStatus($user, $badgeInProgress['resource']['status'], $this->badgeValidator);
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
    	
    	$workspaces = $this->entityManager->getRepository("ClarolineCoreBundle:Workspace\AbstractWorkspace")->findAllWorkspacesUserIsRegisteredTo($user);
    	$badgeRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\Badge');
    	 
    	$result = array();
    	 
    	// get all badges associated to dropzone for each session subscribed
    	foreach($workspaces as $workspace) {
    		$badgesInProgress = array();
    		$workspaceBadges = $badgeRepository->findByWorkspace($workspace);
    		foreach($workspaceBadges as $i => $badge) {

    			if ($badge->isSkillBadge()) {
    				$evalNode = $badge->getAssociatedEvaluations();
    				$badgeInProgress = array();
    				$badgeInProgress['badge'] = $badge;
    				$badgeInProgress['resource'] = array();
    				$badgeInProgress['resource']['url'] = $this->getResourceUrlAssociatedWithRule( $badge, $evalNode[0]->getResourceType()->getName() );
    				$badgeInProgress['resource']['resource'] = $this->getResourceAssociatedWithBadge( $badge, $evalNode[0]->getResourceType()->getName(), $user );
    				$badgeInProgress['resource']['status'] = $badge->getBadgeResourceStatus($badgeInProgress['resource']['resource']);
    					
    				$status = $badge->getBadgeStatus($user, $badgeInProgress['resource']['status'], $this->badgeValidator);
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
    

    /*
     *  @return instance of Claroline\CoreBundle\Entity\Resource\ResourceNode if resource ID found
     */
    public function getResourceAssociatedWithBadge(Badge $badge, $resourceType, $loggedUser ) {
    	$associatedResource = array();
    
    	if ( strpos( $resourceType, 'dropzone' ) ) {
    		$evalDropzoneRepo = $this->entityManager->getRepository('IcapDropzoneBundle:Dropzone');
    		$evalDropRepo = $this->entityManager->getRepository('IcapDropzoneBundle:Drop');
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
    		$exercisesRepo = $this->entityManager->getRepository('UJMExoBundle:Exercise');
    		foreach ( $badgeRules = $badge->getRules() as $BadgeRule ) {
    			if ( strpos( $BadgeRule->getAction(), $resourceType ) ) {
    				$badgeRessourceNode = $BadgeRule->getResource();
    				if ( $badgeRessourceNode ) {
    					$associatedExercise = $exercisesRepo->findOneByResourceNode( $badgeRessourceNode );
    					$mark = $exercisesRepo->getExerciseMarksForUser($associatedExercise, $loggedUser);
    					$maxMark = $this->exerciseService->getExerciseTotalScore($associatedExercise->getId());
    					$mark = ($mark / $maxMark) * 20;
    					$associatedResource = array( 'exercise' => $associatedExercise, 'bestMark' => $mark);
    				}
    			}
    		}
    	}
    	return $associatedResource;
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

    	foreach ( $badgeRules = $badge->getRules() as $i => $BadgeRule ) {
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
    	return $this->router->generate('claro_resource_open', array(
    			'node' => $resource->getId(),
    			'resourceType' => $resource->getResourceType()->getName()
    	));
    }
}
