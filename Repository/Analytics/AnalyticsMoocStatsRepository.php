<?php

namespace Claroline\CoreBundle\Repository\Analytics;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;

/**
 * AnalyticsMoocStatsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnalyticsMoocStatsRepository extends EntityRepository {
	
	
	public function avgConnectionsForSession(MoocSession $moocSession) {
		$workspace = $moocSession->getMooc()->getWorkspace();
		$from = $moocSession->getStartDate();
		$to = $moocSession->getEndDate();
		
		$dql = "SELECT AVG(ams.nbConnections)
				FROM Claroline\CoreBundle\Entity\Analytics\AnalyticsMoocStats ams
				WHERE ams.workspace = :workspace
				AND ams.date >= :from
				AND ams.date <= :to";
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"from" => $from,
				"to" => $to
		));
		
		return $query->getSingleScalarResult();
	}
	
	public function countSubscriptionsForSession(MoocSession $moocSession) {
		$workspace = $moocSession->getMooc()->getWorkspace();
		$from = $moocSession->getStartInscriptionDate();
		$to = $moocSession->getEndInscriptionDate();
	
		$dql = "SELECT SUM(ams.nbSubscriptions)
				FROM Claroline\CoreBundle\Entity\Analytics\AnalyticsMoocStats ams
				WHERE ams.workspace = :workspace
				AND ams.date >= :from
				AND ams.date <= :to";
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"from" => $from,
				"to" => $to
		));
	
		return $query->getSingleScalarResult();
	}
	

	public function getConnectionsForToday(AbstractWorkspace $workspace) {
		$date = new \DateTime("today midnight");
		$dql = "SELECT ams.nbConnections
				FROM Claroline\CoreBundle\Entity\Analytics\AnalyticsMoocStats ams
				WHERE ams.workspace = :workspace
				AND ams.date = :date";
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"date" => $date
		));
	
		return $query->getSingleScalarResult();
	}
	
	public function getSubscriptionsAndConnectionsByDay(MoocSession $moocSession) {
		$workspace = $moocSession->getMooc()->getWorkspace();
		$from = $moocSession->getStartInscriptionDate();
		$to = $moocSession->getEndDate();
		
		$dql = "SELECT ams.date AS date,
					ams.nbSubscriptions AS nbSubscriptions,
					ams.nbConnections AS nbConnections
				FROM Claroline\CoreBundle\Entity\Analytics\AnalyticsMoocStats ams
				WHERE ams.workspace = :workspace
				AND ams.date >= :from
				AND ams.date <= :to";
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
				"workspace" => $workspace,
				"from" => $from,
				"to" => $to
		));
		
		return $query->getResult();
	}
}
