<?php

namespace Claroline\CoreBundle\Repository\Analytics;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;

/**
 * AnalyticsBadgeMoocStatsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnalyticsBadgeMoocStatsRepository extends EntityRepository {
	
	public function countBadgesSuccessRateForSession(MoocSession $session) {
		$workspace = $session->getMooc()->getWorkspace();
		
		$dql = "SELECT
					b AS badge,
					abms.badgeType AS type,
					SUM(abms.nbSuccess) AS totalSuccess,
					(CASE WHEN (abms.badgeType = 'knowledge') THEN (SUM(abms.nbParticipations) - SUM(abms.nbSuccess))
    					ELSE SUM(abms.nbFail) END) AS totalFail,
					(CASE WHEN (abms.badgeType = 'knowledge') THEN 0
    					ELSE SUM(abms.nbParticipations) - (SUM(abms.nbSuccess) + SUM(abms.nbFail)) END) AS totalParticipations
				FROM Claroline\CoreBundle\Entity\Badge\Badge b
				JOIN Claroline\CoreBundle\Entity\Analytics\AnalyticsBadgeMoocStats abms
					WITH abms.badge = b
				WHERE abms.workspace = :workspace
				GROUP BY badge";
		
		$query = $this->_em->createQuery($dql);
		$query->setParameters(array(
			"workspace" => $workspace			
		));
		
		return $query->getResult();
	}
}
