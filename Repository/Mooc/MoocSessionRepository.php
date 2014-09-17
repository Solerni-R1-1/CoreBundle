<?php

namespace Claroline\CoreBundle\Repository\Mooc;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * MoocSessionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MoocSessionRepository extends EntityRepository
{
	/**
	 * Retrieves the last session the user has joined (a current session if possible)
	 * 
	 * @param ClarolineCoreBundle\Entity\User $user
	 */
	public function getLastMoocSessionForUser($user, $mooc) {
		$query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms 
            	WHERE (:user MEMBER OF ms.users 
                		OR EXISTS (
                			SELECT g FROM Claroline\CoreBundle\Entity\Group g
							JOIN g.moocSessions gms
							WHERE ms = gms
							AND g IN (:groups))) 
				AND ms.mooc = :mooc 
				AND ms.startDate < CURRENT_TIMESTAMP()
				ORDER BY ms.endDate DESC ";
		$groups = array();
		foreach ($user->getGroups() as $group) {
			$groups[] = $group->getId(); 
		}
		$qb = $this->_em->createQuery($query)->setParameters(array(
				"user" => $user,
				"mooc" => $mooc,
				"groups" => $groups
		));
        
        $result = $qb->getResult();
        
        return ( count($result) > 0 ) ? $result[0] : NULL;
		
	}
	
	/**
	 * Guess the mooc session which should be displayed for this workspace and user.
	 * 
	 * @param ClarolineCoreBundle\Entity\AbstractWorkspace $workspace
	 * @param ClarolineCoreBundle\Entity\User $user
	 */
	public function guessMoocSession($workspace, $user) {
		$session = new Session();
		if ($workspace->isMooc()) {
			$mooc = $workspace->getMooc();
			$moocSessions = $mooc->getMoocSessions();
			if ($session->has($mooc->getId().'moocSession')) {
				$moocSessionId = $session->get($mooc->getId().'moocSession');
				foreach($moocSessions as $ms) {
					if ($ms->getId() == $moocSessionId) {
						$moocSession = $ms;
						break;
					}
				}
			} else {
				$moocSession = $this->getLastMoocSessionForUser($user, $mooc);
			}
		} else {
			$moocSession = null;
		}
		
		return $moocSession;
	}
    
     /**
	 * Retrieves the active session for a mooc or the next one if empty
	 * 
	 * @param ClarolineCoreBundle\Entity\Mooc\Mooc $mooc
	 */
	public function getActiveMoocSessionForUser( $mooc, $user ) {
        
        if( $user ) {
            // Get the firest active mooc where the user is in
            $query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms 
                    WHERE (:user MEMBER OF ms.users 
                		OR EXISTS (
                			SELECT g FROM Claroline\CoreBundle\Entity\Group g
							JOIN g.moocSessions gms
							WHERE ms = gms
							AND g IN (:groups))) 
                    AND ms.mooc = :mooc 
                    AND ms.startDate < CURRENT_TIMESTAMP()
                    AND ms.endDate > CURRENT_TIMESTAMP()
                    ORDER BY ms.startDate DESC ";
			$groups = array();
			foreach ($user->getGroups() as $group) {
				$groups[] = $group->getId(); 
			}
            $qb = $this->_em->createQuery($query)->setParameters(array(
                    "mooc"		=> $mooc,
                    "user"		=> $user,
					"groups"	=> $groups
            ));
            $result = $qb->getResult();
        }

        if ( count( $result ) == 0 ) {
            // Get the most recent session where we are inside the inscription window
            $query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms " .
                    "WHERE ms.mooc = :mooc " .
                    "AND ms.startInscriptionDate < CURRENT_TIMESTAMP()" .
                    "AND ms.endInscriptionDate > CURRENT_TIMESTAMP()" .
                    "ORDER BY ms.startInscriptionDate DESC ";
            $qb = $this->_em->createQuery($query)->setParameters(array(
                    "mooc" => $mooc
            ));
            $result = $qb->getResult();
        }

        if ( count($result) == 0 ) {
            // Get the closest next session in time to possible preinscription
            $query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms " .
                    "WHERE ms.mooc = :mooc " .
                    "AND ms.startInscriptionDate > CURRENT_TIMESTAMP()" .
                    "ORDER BY ms.startInscriptionDate DESC ";
            $qb = $this->_em->createQuery($query)->setParameters(array(
                    "mooc" => $mooc
            ));
            $result = $qb->getResult();
        }

        if ( count($result) == 0 ) {
            // Get the first session 
            $query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms " .
                    "WHERE ms.mooc = :mooc " .
                    "ORDER BY ms.endDate DESC ";
            $qb = $this->_em->createQuery($query)->setParameters(array(
                    "mooc" => $mooc
            ));
            $result = $qb->getResult();
        }

        return ( count($result) > 0 ) ? $result[0] : NULL;
		
	}
    
    /*
	 * Guess the mooc active session : the active one or the next one
	 * 
	 * @param ClarolineCoreBundle\Entity\AbstractWorkspace $workspace
	 * @param ClarolineCoreBundle\Entity\User $user
     */
    public function guessActiveMoocSession( $workspace, $user ) {
        return $this->getActiveMoocSessionForUser( $workspace->getMooc(), $user );
    }
    
    public function getMoocSessionByForum($forum) {
    	$query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms ".
    			"WHERE ms.forum = :forum";
    	$qb = $this->_em->createQuery($query)->setParameters(array(
    			"forum" => $forum->getResourceNode()
    	));
    	
    	$result = $qb->getResult();
    	
    	return ( count($result) > 0 ) ? $result[0] : NULL;
    }
    
    public function isUserRegisteredToForumSession($forum, $user) {

    	$query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms 
    			WHERE ms.forum = :forum
    			AND :user MEMBER OF ms.users";
    	$qb = $this->_em->createQuery($query)->setParameters(array(
    			"forum" => $forum,
    			"user" => $user
    	));
    	 
    	$result = $qb->getResult();
    	 
    	return ( count($result) > 0 ) ? true : false;
    }
    
    /**
     * Returns the active session, or the last one if no active session.
     * @param AbstractWorkspace $workspace
     */
    public function getActiveOrLastSession(AbstractWorkspace $workspace) {
    	$query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms
				WHERE ms.mooc = :mooc 
				AND ms.startDate < CURRENT_TIMESTAMP()
				ORDER BY ms.endDate DESC ";
		$qb = $this->_em->createQuery($query)->setParameters(array(
				"mooc" => $workspace->getMooc()
		));
        
        $result = $qb->getResult();
        
        return ( count($result) > 0 ) ? $result[0] : NULL;
    }
}
