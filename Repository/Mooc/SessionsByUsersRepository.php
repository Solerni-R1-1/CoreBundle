<?php

namespace Claroline\CoreBundle\Repository\Mooc;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * MoocSessionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SessionsByUsersRepository extends EntityRepository
{

	/**
	 *
	 *
	 * @param moocAccessConstraints
	 * @param user
	 *
	 * @return integer number of entity deleted
	 *
	 **/
	public function deleteByConstraintIdAndUserId(MoocAccessConstraints $moocAccessConstraints = null, User $user = null) {

		$qb = $this->_em->createQueryBuilder();
		$qb->delete('ClarolineCoreBundle:Mooc\SessionsByUsers', 's');

		if($moocAccessConstraints != null){
			$qb->andWhere($qb->expr()->eq('s.moocAccessConstraints', ':moocAccessConstraints'));
			$qb->setParameter(':moocAccessConstraints', $moocAccessConstraints);
		}

		if($user != null){
			$qb->andWhere($qb->expr()->eq('s.user', ':user'));
			$qb->setParameter(':user', $user);
		}
		$query = $qb->getQuery();

		$numDeleted = $query->execute();

		return $numDeleted;
	}

	/**
	 *
	 *
	 * @param AbstractWorkspace
	 *
	 * @return integer number of entity deleted
	 *
	 **/
	public function deleteAllByWorkspace(AbstractWorkspace $workspace) {
		$qb = $this->_em->createQueryBuilder();
		$qb->delete('ClarolineCoreBundle:Mooc\SessionsByUsers', 's');
		
		$sessionsIds[] = array();
		foreach ($workspace->getMooc()->getMoocSessions() as $session) {
			$sessionsIds[] = $session->getId();
		}
		
		
		$qb->where('s.moocSession IN (:sessionsIds)');
		$qb->setParameter('sessionsIds', $sessionsIds);
		$qb->getQuery()->execute();
	}
    
    public function getConstraintRowsNotMatchUsersList( $constraintId, array $usersList ) {
        
        $dql = "SELECT s.id, IDENTITY(s.user) as user
    			FROM Claroline\CoreBundle\Entity\Mooc\SessionsByUsers s
                WHERE s.moocAccessConstraints = (:constraintId)";
        
        if ( count( $usersList ) > 0 ) {
            $dql .= "AND s.user NOT IN (:usersList)";
        }
        
        $query = $this->_em->createQuery($dql);
        $query->setParameter("constraintId", $constraintId);
        
        if ( count( $usersList ) > 0 ) {
            $query->setParameter("usersList", $usersList);
        }
        
        return  $query->getScalarResult();
        
    }
    
    public function getListofUsersAlreadyPresent( $constraintId, array $usersList, $mooc = null ) {
        
        $dql = "SELECT IDENTITY(s.user) as user
    			FROM Claroline\CoreBundle\Entity\Mooc\SessionsByUsers s
                WHERE s.user IN (:usersList)
                AND s.moocAccessConstraints = (:constraintId)";
        
        if ( $mooc ) {
            
            $sessions = array();
            foreach ( $mooc->getMoocSessions() as $session ) {
                $sessions[] = $session;
            }
            
            $dql .= "AND s.moocSession IN (:moocSessions)";
        }
        
        $query = $this->_em->createQuery($dql);
        $query->setParameter("usersList", $usersList);
        $query->setParameter("constraintId", $constraintId);
        
        if ( $mooc ) {
            $query->setParameter("moocSessions", $sessions);
        }
        
        return  $query->getScalarResult();
        
    }
    
    /*
     * Arrays of ids
     */
    public function deleteRowsFromIds( array $rowstoDelete ) {
        
        $dql = "DELETE Claroline\CoreBundle\Entity\Mooc\SessionsByUsers s
                WHERE s.id IN (:rowsToDelete)";
        
       $query = $this->_em->createQuery($dql);
       $query->setParameter("rowsToDelete", $rowstoDelete);
       
       return $query->execute();
    }
    
   
}
