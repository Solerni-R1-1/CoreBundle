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

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\User;

class MessageRepository extends NestedTreeRepository
{
    /**
     * Returns the ancestors of a message (the message itself is also returned).
     *
     * @param Message $message
     *
     * @return array[Message]
     */
    public function findAncestors(Message $message)
    {
        $level = $message->getLvl() + 1;
        $dql = "
            SELECT m FROM Claroline\CoreBundle\Entity\Message m
            WHERE m.lft BETWEEN m.lft AND m.rgt
            AND m.root = {$message->getRoot()}
            AND m.lvl <= {$level}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

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
            JOIN um.user u
            WHERE u.id = {$user->getId()}
            AND um.isRead = false
            AND um.isRemoved = false
            AND (m.user IS NULL OR m.user != :user)
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter("user", $user);
        $result = $query->getSingleScalarResult();

        //?? getFirstResult and aliases do not work. Why ?
        return $result;
    }
}
