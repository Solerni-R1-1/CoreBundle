<?php

namespace Claroline\CoreBundle\Repository\Mooc;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;

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
	public function getLastMoocSessionForUser(User $user, Mooc $mooc) {

        // Get user and define Mooc Admin Roles
        $userRoles = $user->getRoles();
        $workspaceAdminRole = array(
           'ROLE_ADMIN',
           'ROLE_WS_CREATOR',
           'ROLE_WS_MANAGER_' . $mooc->getWorkspace()->getGuid(),
        );

        $hasAdminRole = false;
        foreach ( $userRoles as $userRole ) {
            if ( in_array( $userRole, $workspaceAdminRole ) ) {
                $hasAdminRole = true;
            }
        }

        // Different query for Mooc Admin Roles
        if ( $hasAdminRole ) {
            $query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms
            	WHERE (:user MEMBER OF ms.users
                		OR EXISTS (
                			SELECT g FROM Claroline\CoreBundle\Entity\Group g
							JOIN g.moocSessions gms
							WHERE ms = gms
							AND g IN (:groups)))
				AND ms.mooc = :mooc
				ORDER BY ms.endDate DESC ";
        } else {
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
        }

		$groups = array();
        if ( $user instanceof User ) {
            foreach ($user->getGroups() as $group) {
                $groups[] = $group->getId();
            }
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
		if ($user instanceof User && $workspace->isMooc()) {
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

        $result= array();

        if( $user ) {
            // Get the first active mooc where the user is in
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
        	if ( $user instanceof User ) {
				foreach ($user->getGroups() as $group) {
					$groups[] = $group->getId();
				}
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

    /**
     * Returns the active session, or the last one if no active session for a given mooc ID.
     * @param int $moocId The mooc id
     */
    public function getActiveOrLastSessionForMoocId($moocId) {
        $query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms
				WHERE ms.mooc = :mooc
				AND ms.startDate < CURRENT_TIMESTAMP()
				ORDER BY ms.endDate DESC ";
        $qb = $this->_em->createQuery($query)->setParameters(array(
            "mooc" => $moocId
        ));

        $result = $qb->getResult();

        return ( count($result) > 0 ) ? $result[0] : NULL;
    }

    /**
     * Retrieves the sessions availables for a user wich started less
     * than X days or will start in less than X days
     *
     * @param ClarolineCoreBundle\Entity\User $user
     */
    public function getAvailableSessionAroundToday($user, $nbDaysAround, $nbMaxResults) {
        $query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms
                LEFT JOIN ms.sessionsByUsers us
                LEFT JOIN  ms.mooc  mo
                WHERE ( mo.isPublic = true
                       OR us.user IN (:user)
                       )
                AND ms.startDate >= DATE_SUB(CURRENT_DATE(), :nbDaysAround, 'DAY')
                AND ms.startDate <= DATE_ADD(CURRENT_DATE(), :nbDaysAround, 'DAY')
                AND ms.endDate > CURRENT_DATE()
                AND ms.endInscriptionDate > CURRENT_DATE()
                ORDER BY ms.endDate DESC

                ";


        $qb = $this->_em->createQuery($query)->setParameters(array(
                "user" => $user,
                "nbDaysAround" => $nbDaysAround,
        ));

        $result = $qb->getResult();

        if(count($result) > $nbMaxResults) {
            $result = array_slice($result, 0, $nbMaxResults);
        }

        return $result;

    }

    /*
     * Get the 5 moocs
     * taken into account user rights
     * and sorted by startInscriptionDate
     */
    public function getFiveMoreRecentSessions($user) {

         $query = "SELECT ms FROM Claroline\CoreBundle\Entity\Mooc\MoocSession ms
                LEFT JOIN ms.sessionsByUsers us
                LEFT JOIN  ms.mooc  mo
                WHERE ( mo.isPublic = true
                       OR us.user IN (:user)
                       )
                ORDER BY ms.startInscriptionDate DESC
        ";

        $qb = $this->_em->createQuery($query)
                ->setParameters(array("user" => $user))
                ->setMaxResults(5);

        $result = $qb->getResult();

        return $result;
    }
}
