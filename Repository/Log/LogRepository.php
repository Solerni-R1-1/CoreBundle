<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Log;

use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogRepository extends EntityRepository
{
    /**
     * @param $configs
     * @param $range
     *
     * @return array|null
     */
    public function countByDayThroughConfigs($configs, $range)
    {
        if ($configs === null || count($configs) == 0) {
            return null;
        }

        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->leftJoin('log.workspace', 'workspace')
            ->select('log.shortDateLog as shortDate, count(log.id) as total')
            ->orderBy('shortDate', 'ASC')
            ->groupBy('shortDate');

        $queryBuilder = $this->addConfigurationFilterToQueryBuilder($queryBuilder, $configs);

        return $this->extractChartData($queryBuilder->getQuery()->getResult(), $range);
    }

    public function countByDayFilteredLogs(
        $action,
        $range,
        $userSearch,
        $actionRestriction,
        $workspaceIds = null,
        $unique = false,
        $resourceType = null,
        $resourceNodeIds = null
    )
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->orderBy('shortDate', 'ASC')
            ->groupBy('shortDate');

        if ($unique === true) {
            $queryBuilder->select('log.shortDateLog as shortDate, count(DISTINCT log.doer) as total');
        } else {
            $queryBuilder->select('log.shortDateLog as shortDate, count(log.id) as total');
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $queryBuilder = $this->addUserFilterToQueryBuilder($queryBuilder, $userSearch);
        $queryBuilder = $this->addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType);

        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if ($resourceNodeIds !== null and count($resourceNodeIds) > 0) {
            $queryBuilder = $this->addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds);
        }

        return $this->extractChartData($queryBuilder->getQuery()->getResult(), $range);
    }

    /**
     * @param $configs
     * @param $maxResult
     *
     * @return null|Query
     */
    public function findLogsThroughConfigs($configs, $maxResult = -1)
    {
        if ($configs === null || count($configs) == 0) {
            return null;
        }

        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->leftJoin('log.workspace', 'workspace')
            ->orderBy('log.dateLog', 'DESC');

        $queryBuilder = $this->addConfigurationFilterToQueryBuilder($queryBuilder, $configs);

        if ($maxResult > 0) {
            $queryBuilder->setMaxResults($maxResult);
        }

        return $queryBuilder->getQuery();
    }

    public function findFilteredLogsQuery(
        $action,
        $range,
        $userSearch,
        $actionsRestriction,
        $workspaceIds = null,
        $maxResult = -1,
        $resourceType = null,
        $resourceNodeIds = null
    )
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC');

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, $actionsRestriction);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $queryBuilder = $this->addUserFilterToQueryBuilder($queryBuilder, $userSearch);
        $queryBuilder = $this->addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType);

        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $queryBuilder = $this->addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds);
        }
        if ($resourceNodeIds !== null and count($resourceNodeIds) > 0) {
            $queryBuilder = $this->addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds);
        }

        if ($maxResult > 0) {
            $queryBuilder->setMaxResults($maxResult);
        }

        return $queryBuilder->getQuery();
    }

    public function findFilteredLogs($action, $range, $userSearch, $actionsRestriction, $workspaceIds)
    {
        return $this->findFilteredLogsQuery(
            $action,
            $range,
            $userSearch,
            $actionsRestriction,
            $workspaceIds
        )->getResult();
    }

    //this method is never used and not up to date.
    public function findActionAfterDate(
        $action,
        $date,
        $doerId = null,
        $resourceId = null,
        $workspaceId = null,
        $receiverId = null,
        $roleId = null,
        $groupId = null,
        $toolName = null,
        $userType = null
    )
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->orderBy('log.dateLog', 'DESC')

            ->andWhere('log.action = :action')
            ->setParameter('action', $action)

            ->andWhere('log.dateLog >= :date')
            ->setParameter('date', $date);

        if ($doerId !== null) {
            $queryBuilder
                ->leftJoin('log.doer', 'doer')
                ->andWhere('doer.id = :doerId')
                ->setParameter('doerId', $doerId);
        }

        if ($resourceId !== null) {
            $queryBuilder
                ->leftJoin('log.resource', 'resource')
                ->andWhere('resource.id = :resourceId')
                ->setParameter('resourceId', $resourceId);
        }

        if ($workspaceId !== null) {
            $queryBuilder
                ->leftJoin('log.workspace', 'workspace')
                ->andWhere('workspace.id = :workspaceId')
                ->setParameter('workspaceId', $workspaceId);
        }

        if ($receiverId !== null) {
            $queryBuilder
                ->leftJoin('log.receiver', 'receiver')
                ->andWhere('receiver.id = :receiverId')
                ->setParameter('receiverId', $receiverId);
        }

        if ($roleId !== null) {
            $queryBuilder
                ->leftJoin('log.role', 'role')
                ->andWhere('role.id = :roleId')
                ->setParameter('roleId', $roleId);
        }

        if ($groupId !== null) {
            $queryBuilder
                ->leftJoin('log.receiverGroup', 'receiverGroup')
                ->andWhere('receiverGroup.id = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if ($toolName !== null) {
            $queryBuilder
                ->andWhere('log.toolName = :toolName')
                ->setParameter('toolName', $toolName);
        }

        $q = $queryBuilder->getQuery();
        $logs = $q->getResult();

        return $logs;
    }

    public function topWSByAction($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('ws.id, ws.name, ws.code, count(log.id) AS actions')
            ->leftJoin('log.workspace', 'ws')
            ->groupBy('ws')
            ->orderBy('actions', 'DESC');

        if ($max > 1) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topMediaByAction($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('node.id, node.name, count(log.id) AS actions')
            ->leftJoin('log.resourceNode', 'node')
            ->leftJoin('log.resourceType', 'resource_type')
            ->andWhere('resource_type.name=:fileType')
            ->groupBy('node')
            ->orderBy('actions', 'DESC')
            ->setParameter('fileType', 'file');

        if ($max > 1) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topResourcesByAction($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('node.id, node.name, count(log.id) AS actions')
            ->leftJoin('log.resourceNode', 'node')
            ->groupBy('node')
            ->orderBy('actions', 'DESC');

        if ($max > 1) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action, null);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function topUsersByAction($range, $action, $max)
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select(
                'doer.id, '
                . "CONCAT(CONCAT(doer.firstName, ' '), doer.lastName) AS name, "
                . 'doer.username, count(log.id) AS actions'
            )
            ->leftJoin('log.doer', 'doer')
            ->groupBy('doer')
            ->orderBy('actions', 'DESC');

        if ($max > 1) {
            $queryBuilder->setMaxResults($max);
        }

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, $action);
        $queryBuilder = $this->addDateRangeFilterToQueryBuilder($queryBuilder, $range);
        $query        = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function activeUsers()
    {
        $queryBuilder = $this
            ->createQueryBuilder('log')
            ->select('COUNT(DISTINCT log.doer) AS users');

        $queryBuilder = $this->addActionFilterToQueryBuilder($queryBuilder, LogUserLoginEvent::ACTION);

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result[0]['users'];
    }

    private function addActionFilterToQueryBuilder(QueryBuilder $queryBuilder, $action, $actionRestriction = null)
    {
        if (null !== $actionRestriction) {
            if ('admin' === $actionRestriction) {
                $queryBuilder->andWhere('log.isDisplayedInAdmin = true');
            } elseif ('workspace' === $actionRestriction) {
                $queryBuilder->andWhere('log.isDisplayedInWorkspace = true');
            }
        }

        if (null !== $action && $action !== 'all') {
            $queryBuilder
                ->andWhere("log.action LIKE :action")
                ->setParameter('action', '%' . $action . '%');
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $range
     *
     * @return QueryBuilder
     */
    private function addDateRangeFilterToQueryBuilder(QueryBuilder $queryBuilder, $range)
    {
        if ($range !== null and count($range) == 2) {
            $startDate = new \DateTime();
            $startDate->setTimestamp($range[0]);
            $startDate->setTime(0, 0, 0);

            $endDate = new \DateTime();
            $endDate->setTimestamp($range[1]);
            $endDate->setTime(23, 59, 59);

            $queryBuilder
                ->andWhere("log.dateLog >= :startDate")
                ->andWhere("log.dateLog <= :endDate")
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $userSearch
     *
     * @return QueryBuilder
     */
    private function addUserFilterToQueryBuilder(QueryBuilder $queryBuilder, $userSearch)
    {
        if ($userSearch !== null && $userSearch !== '') {
            $upperUserSearch = strtoupper($userSearch);
            $upperUserSearch = trim($upperUserSearch);
            $upperUserSearch = preg_replace('/\s+/', ' ', $upperUserSearch);

            $queryBuilder->leftJoin('log.doer', 'doer');
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orx(
                    $queryBuilder->expr()->like('UPPER(doer.lastName)', ':userSearch'),
                    $queryBuilder->expr()->like('UPPER(doer.firstName)', ':userSearch'),
                    $queryBuilder->expr()->like('UPPER(doer.username)', ':userSearch'),
                    $queryBuilder->expr()->like(
                        "CONCAT(CONCAT(UPPER(doer.firstName), ' '), UPPER(doer.lastName))",
                        ':userSearch'
                    ),
                    $queryBuilder->expr()->like(
                        "CONCAT(CONCAT(UPPER(doer.lastName), ' '), UPPER(doer.firstName))",
                        ':userSearch'
                    )
                )
            );

            $queryBuilder->setParameter('userSearch', '%' . $upperUserSearch . '%');
        }

        return $queryBuilder;
    }

    private function addWorkspaceFilterToQueryBuilder($queryBuilder, $workspaceIds)
    {
        if ($workspaceIds !== null and count($workspaceIds) > 0) {
            $queryBuilder->leftJoin('log.workspace', 'workspace');
            if (count($workspaceIds) == 1) {
                $queryBuilder->andWhere('workspace.id = :workspaceId');
                $queryBuilder->setParameter('workspaceId', $workspaceIds[0]);
            } else {
                $queryBuilder->andWhere('workspace.id IN (:workspaceIds)')->setParameter('workspaceIds', $workspaceIds);
            }
        }

        return $queryBuilder;
    }

    private function addResourceFilterToQueryBuilder($queryBuilder, $resourceNodeIds)
    {
        if ($resourceNodeIds !== null and count($resourceNodeIds) > 0) {
            $queryBuilder->leftJoin('log.resourceNode', 'resource');
            if (count($resourceNodeIds) == 1) {
                $queryBuilder->andWhere('resource.id = :resourceId');
                $queryBuilder->setParameter('resourceId', $resourceNodeIds[0]);
            } else {
                $queryBuilder->andWhere('resource.id IN (:resourceNodeIds)')
                    ->setParameter('resourceNodeIds', $resourceNodeIds);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder                                         $queryBuilder
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance[] $configs
     *
     * @return mixed
     */
    private function addConfigurationFilterToQueryBuilder(QueryBuilder $queryBuilder, $configs)
    {
        $actionIndex = 0;
        foreach ($configs as $config) {
            $workspaceId = $config->getWidgetInstance()->getWorkspace()->getId();
            $queryBuilder
                ->where('workspace.id = :workspaceId')
                ->setParameter('workspaceId', $workspaceId);

            if ($config->hasRestriction()) {
                $queryBuilder
                    ->andWhere('log.action IN (:actions)')
                    ->setParameter('actions', $config->getRestrictions());
            }
        }

        return $queryBuilder;
    }

    private function addResourceTypeFilterToQueryBuilder($queryBuilder, $resourceType)
    {
        if (!empty($resourceType)) {
            $queryBuilder
                ->leftJoin('log.resourceType', 'resourceType')
                ->andWhere('resourceType.name = :resourceType')
                ->setParameter('resourceType', $resourceType);
        }

        return $queryBuilder;
    }

    private function extractChartData($result, $range)
    {
        $chartData = array();
        if (count($result) > 0) {
            //We send an array indexed by date dans contains count
            $lastDay = null;
            $endDay = null;
            if ($range !== null and count($range) == 2) {
                $lastDay = new \DateTime();
                $lastDay->setTimestamp($range[0]);

                $endDay = new \DateTime();
                $endDay->setTimestamp($range[1]);
            }

            foreach ($result as $line) {
                if ($lastDay !== null) {
                    while ($lastDay->getTimestamp() < $line['shortDate']->getTimestamp()) {
                        $chartData[] = array($lastDay->getTimestamp() * 1000, 0);
                        $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day
                    }
                } else {
                    $lastDay = $line['shortDate'];
                }
                $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day

                $chartData[] = array($line['shortDate']->getTimestamp() * 1000, intval($line['total']));
            }

            while ($lastDay->getTimestamp() <= $endDay->getTimestamp()) {
                $chartData[] = array($lastDay->getTimestamp() * 1000, 0);

                $lastDay->add(new \DateInterval('P1D')); // P1D means a period of 1 day
            }
        }

        return $chartData;
    }

    /**
     * @return QueryBuilder
     */
    public function defaultQueryBuilderForBadge()
    {
        return $this->createQueryBuilder('l')->orderBy('l.dateLog');
    }

    public function getSubscribeCountUntil(AbstractWorkspace $workspace, \DateTime $until) {
    	$qb = $this->createQueryBuilder('l')
    	->where("l.workspace = :workspace")
    	->andWhere("l.dateLog < :until")
    	->setParameters(array(
    			"workspace" => $workspace,
    			"until" => $until
    	));
    
    	return count($qb->getQuery()->getResult());
    }
    
    public function findAllBetween(AbstractWorkspace $workspace, \DateTime $from, \DateTime $to, $action, $filteredRoles = array()) {
    	$parameters = array(
    		"from" => $from,
    		"to" => $to,
    		"action" => $action,
    		"workspace" => $workspace
    	);
    	if (count($filteredRoles) > 0) {
    		$dql = "SELECT l
    				FROM Claroline\CoreBundle\Entity\Log\Log l
    				WHERE l.dateLog >= :from
    				AND l.dateLog <= :to
    				AND l.action IN (:action)
    				AND l.workspace = :workspace
    				AND (l.receiver IS NULL
    				OR l.receiver NOT IN (
    					SELECT u FROM Claroline\CoreBundle\Entity\User u
    					JOIN u.roles as r
    					WHERE r.name IN (:roles)))";
    		$parameters['roles'] = $filteredRoles;
    	} else {
    		$dql = "SELECT l
    				FROM Claroline\CoreBundle\Entity\Log\Log l
    				WHERE l.dateLog >= :from
    				AND l.dateLog <= :to
    				AND l.action IN (:action)
    				AND l.workspace = :workspace";
    	}
    	
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters($parameters);
    	
    	return $query->getResult();
    }

    public function countActiveUsersSinceDate(AbstractWorkspace $workspace, $date, $filteredRoles = array()) {
    	$parameters = array(
    			"workspace" => $workspace,
    			"date" => $date
    	);
    	if (count($filteredRoles) > 0) {
    		$dql = "SELECT COUNT(DISTINCT l.doer) FROM Claroline\CoreBundle\Entity\Log\Log l
    				WHERE l.workspace = :workspace
    				AND l.dateLog > :date
    				AND l.doer NOT IN (
    					SELECT u FROM Claroline\CoreBundle\Entity\User u
    					JOIN u.roles r
    					WHERE r IN (:roles))";
    		$parameters['roles'] = $filteredRoles;
    	} else {
    		$dql = "SELECT COUNT(DISTINCT l.doer) FROM Claroline\CoreBundle\Entity\Log\Log l
    				WHERE l.workspace = :workspace
    				AND l.dateLog > :date";
    	}
    	
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters($parameters);
    	
    	 
    	return $query->getSingleScalarResult();
    }

    public function countActiveGroupsUsersSinceDate(AbstractWorkspace $workspace, $date, $filteredRoles = array()) {
    	$parameters = array(
    			"workspace" => $workspace,
    			"date" => $date
    	);
    	if (count($filteredRoles) > 0) {
	    	$dql = "SELECT COUNT(DISTINCT u)
	    			FROM Claroline\CoreBundle\Entity\Group g
	    			JOIN g.users u
	    			JOIN Claroline\CoreBundle\Entity\Log\Log l
	    				WITH l.receiverGroup = g
	    			WHERE l.workspace = :workspace
	    			AND l.dateLog > :date
	    			AND u NOT IN (
    					SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
    					JOIN u2.roles r
    					WHERE r IN (:roles))";
    		$parameters['roles'] = $filteredRoles;
    	} else {
    		$dql = "SELECT COUNT(DISTINCT u)
	    			FROM Claroline\CoreBundle\Entity\Group g
	    			JOIN g.users u
	    			JOIN Claroline\CoreBundle\Entity\Log\Log l
	    				WITH l.receiverGroup = g
	    			WHERE l.workspace = :workspace
	    			AND l.dateLog > :date";
    	}
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters($parameters);
    	 
    	return $query->getSingleScalarResult();
    }

    public function countRegisteredUsers(AbstractWorkspace $workspace) {
    	$qb = $this->createQueryBuilder('l')
    	->select("COUNT(DISTINCT l.doer)")
    	->where("l.workspace = :workspace")
    	->setParameters(array(
    			"workspace" => $workspace
    	));
    
    	return $qb->getQuery()->getSingleScalarResult();
    }

    public function countLogsUsersTodayByAction(AbstractWorkspace $workspace, $action, $excludeRoles = null) {
    	$todayAtMidnight = new \DateTime('today midnight');
    	$parameters = array(
    			"workspace" 		=> $workspace,
    			"action"			=> $action,
    			"todayAtMidnight" 	=> $todayAtMidnight
    	);
    	$dql = "SELECT COUNT(DISTINCT l.doer)
    			FROM Claroline\CoreBundle\Entity\Log\Log l
    			WHERE l.workspace = :workspace
    			AND l.action = :action
    			AND l.dateLog >= :todayAtMidnight ";
    	
    	if ($excludeRoles != null) {
    		$dql .= " AND l.doer NOT IN (
	    				SELECT u
	    				FROM Claroline\CoreBundle\Entity\User u
	    				JOIN u.roles r
	    				WHERE r.name IN (:roles))";
    		$parameters['roles'] = $excludeRoles;
    	}
    	$query = $this->_em->createQuery($dql);
    	$query ->setParameters($parameters);
    
    	return $query->getSingleScalarResult();
    }


    public function countLogsUsersActionByDate(AbstractWorkspace $workspace, $action) {
    	$qb = $this->createQueryBuilder('l')
    	->select("COUNT(DISTINCT l.doer) as number, l.dateLog")
    	->where("l.workspace = :workspace")
    	->andWhere("l.action = :action")
    	->groupBy("l.dateLog")
    	->setParameters(array(
    			"workspace" 		=> $workspace,
    			"action"			=> $action
    	));
        		
    	$result = $qb->getQuery()->getResult();
    	return $result;
    }

    public function countAllLogsByUsers(AbstractWorkspace $workspace, $filteredRoles = array()) {
    	$users = $workspace->getAllUsers($filteredRoles);
    	$dql = "
	    	SELECT count(l) as nbLogs, u as user FROM Claroline\CoreBundle\Entity\User u 
	    	JOIN Claroline\CoreBundle\Entity\Log\Log l
    		WHERE l.receiver = u
    		AND l.workspace = :workspace
    		AND u IN (:users)
    		GROUP BY user
    		ORDER BY nbLogs DESC";
    
    
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters(array(
    			"workspace" 		=> $workspace,
    			"users"				=> $users
    	));
    
    	$result = $query->getResult();
    	return $result;
    }
    
	public function getLastConnection(AbstractWorkspace $workspace, User $user) {
    	$qb = $this->createQueryBuilder('l')
	    	->select("l")
	    	->where("l.workspace = :workspace")
	    	->andWhere("l.doer = :user")
	    	->andWhere("l.action = 'workspace-enter'")
	    	->orderBy("l.dateLog", "DESC")
	    	->setParameters(array(
	    			"workspace" => $workspace,
	    			"user" => $user
	    	));
    	 
	    $result = $qb->getQuery()->getResult(); 
    	return count($result) > 0 ? $result[0] : null;
    } 
    


    public function getLastConnectionAndSubscriptionForWorkspace(AbstractWorkspace $workspace, array $excludeRoles) {
    	$dql = "SELECT u.id FROM Claroline\CoreBundle\Entity\User u
    			JOIN u.roles r
    			WHERE r.name IN (:roles)";
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("roles", $excludeRoles);
    	$excludeUsers = $query->getResult();

    	$dql = "SELECT u.lastName,
    				u.firstName,
    				u.username,
    				u.mail,
    				l1.shortDateLog AS subscriptionDate,
    				CASE WHEN l2 IS NOT NULL THEN MAX(l2.shortDateLog) ELSE 'N/A' END AS connectionDate
    			FROM Claroline\CoreBundle\Entity\User u
    			JOIN Claroline\CoreBundle\Entity\Log\Log l1
    				WITH l1.workspace = :workspace
    				AND l1.receiver = u
    				AND l1.action = 'workspace-role-subscribe_user'
    			LEFT JOIN Claroline\CoreBundle\Entity\Log\Log l2
    				WITH l2.workspace = :workspace
    				AND l2.doer = u
    				AND l2.action = 'workspace-enter'
    			WHERE u.id NOT IN (:excludeUsers)
    			GROUP BY u.id";
    	
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("workspace", $workspace);
    	$query->setParameter("excludeUsers", $excludeUsers);
    	
    	$result = $query->getScalarResult();

    	$dql = "SELECT u.lastName,
    				u.firstName,
    				u.username,
    				u.mail,
    				l1.shortDateLog AS subscriptionDate,
    				CASE WHEN l2 IS NOT NULL THEN MAX(l2.shortDateLog) ELSE 'N/A' END AS connectionDate
    			FROM Claroline\CoreBundle\Entity\User u
    			JOIN u.groups g
    			JOIN Claroline\CoreBundle\Entity\Log\Log l1
    				WITH l1.workspace = :workspace
    				AND l1.receiverGroup = g
    				AND l1.action = 'workspace-role-subscribe_group'
    			LEFT JOIN Claroline\CoreBundle\Entity\Log\Log l2
    				WITH l2.workspace = :workspace
    				AND l2.doer = u
    				AND l2.action = 'workspace-enter'
    			WHERE u.id NOT IN (:excludeUsers)
    			GROUP BY u.id";

    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("workspace", $workspace);
    	$query->setParameter("excludeUsers", $excludeUsers);
    	 
    	$result = array_merge($result, $query->getScalarResult());
    	
    	return $result;
    }

    public function getLastSubscription(AbstractWorkspace $workspace, User $user) {
    	$dql = "SELECT l FROM Claroline\CoreBundle\Entity\Log\Log l
    			WHERE 
    				((l.action = 'workspace-role-subscribe_user' AND l.receiver = :user)
    				OR (l.action = 'workspace-role-subscribe_group' AND l.receiverGroup IN (:groups)))
    			AND l.workspace = :workspace
    			
    			ORDER BY l.dateLog DESC";
    	
    	$query = $this->_em->createQuery($dql);
    	$groups = array();
    	foreach ($user->getGroups() as $group) {
    		$groups[] = $group;
    	}
    	$query->setParameters(array(
	    		"workspace" => $workspace,
	    		"user" => $user,
    			"groups" => $groups
	    	));
    
    	$result = $query->getResult();
    	
    	if (count($result) > 0) {
    		return $result[0];
    	} else { 
    		return null;
    	}
    }
    
    public function countLogsByDay(
    		AbstractWorkspace $workspace, $from, $to, $excludeRoles = array()) {
    	$parameters = array(
    		"from" => $from,
    		"to" => $to,
    		"workspace" => $workspace
    	);
    	$dql = "SELECT l.action AS action,
    				l.shortDateLog AS shortDate,
    				COUNT(l.doer) AS nbDoers,
    				COUNT(l.receiver) AS nbReceivers,
    				COUNT(u) AS nbGroupReceivers,
    				SUBSTRING(l.dateLog, 12, 2) AS hour
    			FROM Claroline\CoreBundle\Entity\Log\Log l
    			LEFT JOIN l.receiverGroup g
    			LEFT JOIN g.users u
    			WHERE l.dateLog >= :from
    			AND l.dateLog <= :to
    			AND l.workspace = :workspace
    			AND (
	    				(l.receiverGroup IS NOT NULL
	    				AND u NOT IN ( 
	    					SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
	    					JOIN u2.roles as r
	    					WHERE r.name IN (:roles)))
    				OR 
    					(l.receiver IS NOT NULL
    					AND l.receiver NOT IN (
		   					SELECT u3 FROM Claroline\CoreBundle\Entity\User u3
		   					JOIN u3.roles as r2
		   					WHERE r2.name IN (:roles)))
    				OR 
    					(l.receiver IS NULL AND l.receiverGroup IS NULL
    					AND l.doer NOT IN(
		   					SELECT u4 FROM Claroline\CoreBundle\Entity\User u4
		   					JOIN u4.roles as r3
		   					WHERE r3.name IN (:roles)))
    				)
    				
    			GROUP BY l.shortDateLog, l.action, hour
    			ORDER BY l.shortDateLog";
    	$parameters['roles'] = $excludeRoles;

    	$query = $this->_em->createQuery($dql);
    	$query->setParameters($parameters);
    	
    	return $query->getResult();
    }
    
    public function getAllActions() {
    	$dql = "SELECT DISTINCT l.action FROM Claroline\CoreBundle\Entity\Log\Log l";
    	$query = $this->_em->createQuery($dql);
    	return $query->getResult();
    }
    
    public function getPreparationForUserAnalytics(AbstractWorkspace $workspace, $from, $to, $action, $excludeRoles = array()) {
    	$dql = "SELECT u AS doer,
    				l.shortDateLog AS date,
    				COUNT(l.id) AS nbActivity
    			FROM Claroline\CoreBundle\Entity\User u
    			JOIN Claroline\CoreBundle\Entity\Log\Log l
    				WITH l.doer = u
    			WHERE l.workspace = :workspace
    			AND l.dateLog >= :from
    			AND l.dateLog <= :to
    			AND (l.doer IS NOT NULL
    					AND l.doer NOT IN (
		   					SELECT u3 FROM Claroline\CoreBundle\Entity\User u3
		   					JOIN u3.roles as r2
		   					WHERE r2.name IN (:roles)))
    			AND l.action NOT IN (:action)
    			GROUP BY l.doer, l.shortDateLog";
    	$parameters = array(
    		"from" 		=> $from,
    		"to" 		=> $to,
    		"workspace" => $workspace,
    		"roles"		=> $excludeRoles,
    		"action"	=> $action
    	);

    	$query = $this->_em->createQuery($dql);
    	$query->setParameters($parameters);
    	
    	return $query->getResult();
    }
    
    public function getDetailsForDoerActionResource(User $doer, $action, ResourceNode $resourceNode) {
    	$dql = "SELECT l.details FROM Claroline\CoreBundle\Entity\Log\Log l
    			WHERE l.doer = :doer
    			AND l.action = :action
    			AND l.resourceNode = :resourceNode
    			ORDER BY l.dateLog DESC";
    	
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters(array(
    			"doer" => $doer,
    			"action" => $action,
    			"resourceNode" => $resourceNode
    	));
    	$query->setMaxResults(1);
    	
    	return $query->getOneOrNullResult();
    }
}
