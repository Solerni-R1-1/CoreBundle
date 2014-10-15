<?php

namespace Claroline\CoreBundle\Repository\Analytics;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use Claroline\CoreBundle\Entity\Analytics\AnalyticsUserMoocStats;

/**
 * AnalyticsUserMoocStatsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnalyticsUserMoocStatsRepository extends EntityRepository {
	
	public function countActiveUsersSince(MoocSession $session, $nbDays) {
		$date = new \DateTime("today midnight");
		$date->sub(new \DateInterval("P".$nbDays."D"));
		$dql = "SELECT COUNT(DISTINCT aums.user)
				FROM Claroline\CoreBundle\Entity\Analytics\AnalyticsUserMoocStats aums
				WHERE aums.workspace = :workspace
				AND aums.date >= :date
				AND aums.nbActivity > 0";
		
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $session->getMooc()->getWorkspace(),
				"date"		=> $date
		));
		
		return $query->getSingleScalarResult();
	}
	
	public function getUsersActivity(MoocSession $moocSession) {
		$workspace = $moocSession->getMooc()->getWorkspace();
		$from = $moocSession->getStartDate();
		$to = $moocSession->getEndDate();
		
		$dql = "SELECT
					u.firstName as firstname,
					u.lastName as lastname,
					u.username as username,
					u.mail as mail, 
					SUM(aums.nbActivity) as nbActivity
				FROM Claroline\CoreBundle\Entity\User u
				JOIN Claroline\CoreBundle\Entity\Analytics\AnalyticsUserMoocStats aums
					WITH aums.user = u
				WHERE (
					aums IS NULL 
					OR (aums.workspace = :workspace
						AND aums.date >= :from
						AND aums.date <= :to))
				GROUP BY u
				ORDER BY nbActivity DESC";
		
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"from"		=> $from,
				"to"		=> $to
		));
		
		return $query->getResult();
	}
	
	public function countTotalForumMessagesForSession(MoocSession $moocSession) {
		$workspace = $moocSession->getMooc()->getWorkspace();
		$from = $moocSession->getStartDate();
		$to = $moocSession->getEndDate();
		
		$dql = "SELECT SUM(aums.nbPublicationsForum)
				FROM Claroline\CoreBundle\Entity\Analytics\AnalyticsUserMoocStats aums
				WHERE aums.workspace = :workspace
				AND aums.date >= :from
				AND aums.date <= :to";
		

		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"from"		=> $from,
				"to"		=> $to
		));
		
		return $query->getSingleScalarResult();
	}
	


	public function countAverageForumMessagesForSession(MoocSession $moocSession) {
		$workspace = $moocSession->getMooc()->getWorkspace();
		$from = $moocSession->getStartDate();
		$to = $moocSession->getEndDate();
	
		$dql = "SELECT AVG(aums.nbPublicationsForum)
				FROM Claroline\CoreBundle\Entity\Analytics\AnalyticsUserMoocStats aums
				WHERE aums.workspace = :workspace
				AND aums.date >= :from
				AND aums.date <= :to";
	
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"from"		=> $from,
				"to"		=> $to
		));
	
		return $query->getSingleScalarResult();
	}

	public function countDailyForumMessagesForSession(MoocSession $moocSession) {
		$workspace = $moocSession->getMooc()->getWorkspace();
		$from = $moocSession->getStartDate();
		$to = $moocSession->getEndDate();
	
		$dql = "SELECT
					SUM(aums.nbPublicationsForum) AS nbPublicationsForum,
					aums.date AS date
				FROM Claroline\CoreBundle\Entity\Analytics\AnalyticsUserMoocStats aums
				WHERE aums.workspace = :workspace
				AND aums.date >= :from
				AND aums.date <= :to
				GROUP BY aums.date
				ORDER BY aums.date";
	
	
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"from"		=> $from,
				"to"		=> $to
		));
	
		return $query->getResult();
	}

	public function countForumMessagesForSessionByUsers(MoocSession $moocSession) {
		$workspace = $moocSession->getMooc()->getWorkspace();
		$from = $moocSession->getStartDate();
		$to = $moocSession->getEndDate();
		
		$dql = "SELECT
					u.firstName as firstname,
					u.lastName as lastname,
					u.username as username,
					u.mail as mail,
					SUM(aums.nbPublicationsForum) as nbPublicationsForum
				FROM Claroline\CoreBundle\Entity\User u
				JOIN Claroline\CoreBundle\Entity\Analytics\AnalyticsUserMoocStats aums
					WITH aums.user = u
				WHERE (
					aums IS NULL 
					OR (aums.workspace = :workspace
						AND aums.date >= :from
						AND aums.date <= :to))
				GROUP BY u
				ORDER BY nbPublicationsForum DESC";
		
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"from"		=> $from,
				"to"		=> $to
		));
		
		return $query->getResult();
	}
}
