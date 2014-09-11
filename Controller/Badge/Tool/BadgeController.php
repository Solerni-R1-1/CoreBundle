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
use Claroline\CoreBundle\Manager\BadgeManager;
use JMS\DiExtraBundle\Annotation as DI;

class BadgeController extends Controller
{
	/** @var BadgeManager */
	private $badgeManager;
	
	/**
	 * @DI\InjectParams({
	 *     "badgeManager" = @DI\Inject("claroline.manager.badge")
	 * })
	 */
	public function __construct(BadgeManager $badgeManager) {
		$this->badgeManager  = $badgeManager;
	}
	
    /*
     * Returns or render (depending of needEcho) lists of badges related to resource_types
     */
    public function myWorkspaceBadgeAction(
    		AbstractWorkspace $workspace,
            User $loggedUser) {
    	
    	$badges = $this->badgeManager->getAllBadgesForWorkspace($loggedUser, $workspace);
    	
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
    
    /**
     * Display the page that aggregates all badges associated to a specific resource type
     * 
     * @Route("/mes_evaluations", name="solerni_user_evaluations")
     * @ParamConverter( "user", options={"authenticatedUser" = true })
     */
    public function userEvaluationsPageAction(User $user) {
        
        $result = $this->badgeManager->getAllBadgesInProgress($user);
        
        return $this->render(
            'ClarolineCoreBundle:Mooc:myEvaluationslist.html.twig',
            array( 'WorkspacesBadgeList' => $result )
        );
        
    }
}
