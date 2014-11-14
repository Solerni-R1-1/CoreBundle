<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{
    

    /**
     * Counts the number of unread messages of a user.
     *
     * @param User $user
     *
     * @return integer
     */
    public function countUnread(User $user)
    {
        $dql = "
            SELECT COUNT(m) FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            WHERE um.user = {$user->getId()}
            AND um.isRead = false
            AND um.isRemoved = false
            AND (m.user IS NULL OR m.user != :user)
            AND NOT EXISTS (
                SELECT m2.id FROM Claroline\CoreBundle\Entity\Message m2
                WHERE m2.root = m.root 
                AND m.id < m2.id
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter("user", $user);
        $result = $query->getSingleScalarResult();

        //?? getFirstResult and aliases do not work. Why ?
        return $result;
    }
}
