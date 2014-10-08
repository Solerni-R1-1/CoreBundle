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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;

class BadgeRepository extends EntityRepository
{
    /**
     * @param Badge $badge
     *
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findUsers(Badge $badge, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ub, u
                FROM ClarolineCoreBundle:User u
                JOIN u.userBadges ub
                WHERE ub.badge = :badgeId
                ORDER BY u.lastName ASC'
            )
            ->setParameter('badgeId', $badge->getId());

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param Badge $badge
     * @param User $user
     *
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findUserBadge(Badge $badge, User $user, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ub, u
                FROM ClarolineCoreBundle:User u
                JOIN u.userBadges ub
                WHERE ub.badge = :badgeId
                AND ub.user = :userId
                ORDER BY u.lastName ASC'
            )
            ->setParameter('badgeId', $badge->getId())
            ->setParameter('userId', $user->getId());

        return $executeQuery ? $query->getOneOrNullResult(): $query;
    }

    /**
     * @param User $user
     *
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findByUser(User $user, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, ub, bt
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.userBadges ub
                JOIN b.translations bt
                WHERE ub.user = :userId'
            )
            ->setParameter('userId', $user->getId());

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param null|string $locale
     *
     * @param bool $executeQuery
     *
     * @return QueryBuilder|array
     */
    public function findOrderedByName($locale = null, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('badge')
            ->join('badge.translations', 'bt')
            ->where('bt.locale = :locale')
            ->orderBy('bt.name', 'ASC')
            ->setParameter('locale', $locale);

        return $executeQuery ? $queryBuilder->getQuery()->getResult() : $queryBuilder;
    }

    /**
     * @param string $slug
     *
     * @param bool $executeQuery
     *
     * @return array
     */
    public function findBySlug($slug, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.translations t
                WHERE t.slug = :slug
                ORDER BY t.name ASC'
            )
            ->setParameter('slug', $slug);

        return $executeQuery ? $query->getSingleResult(): $query;
    }

    /**
     * @param string $name
     * @param string $locale
     * @param bool   $executeQuery
     *
     * @return Query|array
     */
    public function findByNameAndLocale($name, $locale, $executeQuery = true)
    {
        $name  = strtoupper($name);
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, t
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.translations t
                WHERE UPPER(t.name) LIKE :name
                AND t.locale = :locale
                ORDER BY t.name ASC'
            )
            ->setParameter('name', "%{$name}%")
            ->setParameter('locale', $locale);

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param string $name
     *
     * @return Badge
     */
    public function findOneByName($name)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, t
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.translations t
                WHERE t.name = :name'
            )
            ->setParameter('name', $name);

        return $query->getSingleResult();
    }

    /**
     * @param string $search
     *
     * @return array
     */
    public function findByNameFrForAjax($search)
    {
        return $this->findByNameForAjax($search, 'fr');
    }

    /**
     * @param string $search
     *
     * @return array
     */
    public function findByNameEnForAjax($search)
    {
        return $this->findByNameForAjax($search, 'en');
    }

    /**
     * @param string $search
     *
     * @param string $locale
     *
     * @return array
     */
    public function findByNameForAjax($search, $locale)
    {
        $resultArray = array();

        /** @var Badge[] $badges */
        $badges = $this->findByNameAndLocale($search, $locale);

        foreach ($badges as $badge) {
            $resultArray[] = array(
                'id'   => $badge->getId(),
                'text' => $badge->getName($locale)
            );
        }

        return $resultArray;
    }

    /**
     * @param  array           $params
     * @return ArrayCollection
     */
    public function extract($params)
    {
        $search = $params['search'];
        if ($search !== null) {

            $query = $this->findByNameAndLocale($search, $params['extra']['locale'], false);

            return $query
                ->setFirstResult(0)
                ->setMaxResults(10)
                ->getResult();
        }

        return array();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param bool                                                     $executeQuery
     *
     * @return Query|array
     */
    public function findByWorkspace(AbstractWorkspace $workspace, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, ub, bt
                FROM ClarolineCoreBundle:Badge\Badge b
                LEFT JOIN b.userBadges ub
                JOIN b.translations bt
                WHERE b.workspace = :workspaceId'
            )
            ->setParameter('workspaceId', $workspace->getId());

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param bool                                                     $executeQuery
     *
     * @return Query|int
     */
    public function dissociateFromWorkspace(AbstractWorkspace $workspace, $executeQuery = true) {

    	$query = $this->getEntityManager()
    	->createQuery(
    			'UPDATE ClarolineCoreBundle:Badge\Badge b
                SET b.workspace = NULL
                WHERE b.workspace = :workspaceId'
    	)
    	->setParameter('workspaceId', $workspace->getId());
    	
    	return $executeQuery ? $query->getResult(): $query;
    }
    
    public function getSkillBadgesParticipationRates(MoocSession $session, array $excludedRoles) {
    	$from = $session->getStartDate();
    	$to = $session->getEndDate();
    	$workspace = $session->getMooc()->getWorkspace();
    	$dql = "
    		SELECT 
    			b AS badge,
    			SUBSTRING(d.dropDate, 1, 10) AS date,
    			COUNT(DISTINCT u.id) AS nbParticipations,
    			'skill' AS type
    		FROM Claroline\CoreBundle\Entity\Badge\Badge b
    		JOIN b.badgeRules br
    		JOIN br.resource res
    		JOIN Icap\DropzoneBundle\Entity\Dropzone dz
    			WITH res = dz.resourceNode
    		JOIN dz.drops d
    		JOIN d.user u
    		JOIN res.resourceType res_type
    		WHERE b.workspace = :workspace
    		AND res_type.name = 'icap_dropzone'
    		AND b.deletedAt IS NULL
    		AND br.action LIKE 'resource-icap_dropzone%'
    		AND u NOT IN (SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
    			JOIN u2.roles AS role
    			WHERE role.name IN (:roles))
    		AND d.dropDate >= :from
    		AND d.dropDate <= :to
    		GROUP BY
    			date,
    			badge";
    	
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("workspace", $workspace);
    	$query->setParameter("roles", $excludedRoles);
    	$query->setParameter("from", $from);
    	$query->setParameter("to", $to);
    	
    	return $query->getResult();
    }
    
    public function getKnowledgeBadgesParticipationRates(MoocSession $session, array $excludedRoles) {
    	$from = $session->getStartDate();
    	$to = $session->getEndDate();
    	$workspace = $session->getMooc()->getWorkspace();
    	$dql = "
    		SELECT
    			b AS badge,
    			SUBSTRING(p.start, 1, 10) AS date,
    			COUNT(DISTINCT u.id) AS nbParticipations,
    			'knowledge' AS type
    		FROM Claroline\CoreBundle\Entity\Badge\Badge b
    		JOIN b.badgeRules br
    		JOIN br.resource res
    		JOIN UJM\ExoBundle\Entity\Exercise e
    			WITH res = e.resourceNode
    		JOIN UJM\ExoBundle\Entity\Paper p
    			WITH p.exercise = e
    		JOIN p.user u
    		JOIN res.resourceType res_type
    		WHERE b.workspace = :workspace
    		AND res_type.name = 'ujm_exercise'
    		AND b.deletedAt IS NULL
    		AND br.action LIKE 'resource-ujm_exercise-exercise%'
    		AND u NOT IN (SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
    			JOIN u2.roles AS role
    			WHERE role.name IN (:roles))
    		AND p.start >= :from
    		AND p.start <= :to
    		GROUP BY
    			date,
    			badge";
    	 
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("workspace", $workspace);
    	$query->setParameter("roles", $excludedRoles);
    	$query->setParameter("from", $from);
    	$query->setParameter("to", $to);
    	 
    	return $query->getResult();
    }
    
    public function getBadgesSuccess(MoocSession $session, array $excludedRoles) {
    	$workspace = $session->getMooc()->getWorkspace();
    	$dql = "SELECT
    				SUBSTRING(ub.issuedAt, 1, 10) AS date,
    				COUNT(DISTINCT u) AS nbSuccess,
    				b AS badge,
    				(CASE WHEN (br.action LIKE 'resource-ujm_exercise-exercise%') THEN 'knowledge'
    				ELSE 'skill' END) type
    			
    			FROM Claroline\CoreBundle\Entity\Badge\Badge b
    			JOIN b.userBadges ub
    			JOIN ub.user u
    			JOIN b.badgeRules br
    			
    			WHERE b.workspace = :workspace
    			AND u NOT IN (SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
	    			JOIN u2.roles AS role
	    			WHERE role.name IN (:roles))
    			AND (br.action LIKE 'resource-ujm_exercise-exercise%'
    				OR
    				br.action LIKE 'resource-icap_dropzone%')
    			
    			GROUP BY date, badge";

    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("workspace", $workspace);
    	$query->setParameter("roles", $excludedRoles);
    	
    	return $query->getResult();
    }

    public function getSkillBadgesFailures(MoocSession $session, array $excludedRoles) {
    	$workspace = $session->getMooc()->getWorkspace();
    	$dql = "
    		SELECT 
    			b AS badge,
    			SUBSTRING(d.dropDate, 1, 10) AS date,
    			COUNT(DISTINCT u.id) AS nbFailures,
    			'skill' AS type
    			
    		FROM Claroline\CoreBundle\Entity\Badge\Badge b
    		JOIN b.badgeRules br
    		JOIN br.resource res
    		JOIN Icap\DropzoneBundle\Entity\Dropzone dz
    			WITH res = dz.resourceNode
    		JOIN dz.drops d
    		JOIN d.user u
    		JOIN res.resourceType res_type
    		WHERE b.workspace = :workspace
    		AND res_type.name = 'icap_dropzone'
    		AND b.deletedAt IS NULL
    		AND br.action LIKE 'resource-icap_dropzone%'
    		AND d.finished = 1
    		AND (SELECT AVG(c.totalGrade) FROM Icap\DropzoneBundle\Entity\Correction c WHERE c.drop = d) 
    			< (SELECT AVG(rule.result) FROM Claroline\CoreBundle\Entity\Badge\BadgeRule AS rule WHERE rule.action = 'resource-icap_dropzone-drop_evaluate' AND rule.associatedBadge = b) 
    		AND u NOT IN (SELECT u2 FROM Claroline\CoreBundle\Entity\User u2
    			JOIN u2.roles AS role
    			WHERE role.name IN (:roles))
    		GROUP BY
    			date,
    			badge";
    
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("workspace", $workspace);
    	$query->setParameter("roles", $excludedRoles);
    	 
    	return $query->getResult();
    }
}
