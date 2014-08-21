<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Badge;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class BadgeRuleRepository extends EntityRepository
{
    /**
     * @param string $action
     * @param bool   $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findBadgeAutomaticallyAwardedFromAction($action, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.badgeRules br
                WHERE br.action = :action
                AND b.automaticAward = true'
            )
            ->setParameter('action', $action);

        return $executeQuery ? $query->getResult(): $query;
    }
    

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param bool                                                     $executeQuery
     *
     * @return Query|int
     */
    public function deleteBadgeRulesAssociatedToWorkspaceResources($workspace, $executeQuery = true) {
    	$resourceIds = array();
    	foreach($workspace->getResources() as $resource) {
    		$resourceIds[] = $resource->getId();
    	}
    	$query = $this->getEntityManager()
    		->createQuery('
    			DELETE
    			FROM ClarolineCoreBundle:Badge\BadgeRule b
    			WHERE b.resource IN (:resourceIds)
    		')
    		->setParameter('resourceIds', $resourceIds);
    		
    	
    	return $executeQuery ? $query->getResult(): $query;
    }
}
