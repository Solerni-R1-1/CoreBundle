<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\GroupRepository")
 * @ORM\Table(
 *      name="claro_group",
*       uniqueConstraints={
 *          @ORM\UniqueConstraint(name="group_unique_name", columns={"name"})
 *      }
 *  )
 * @DoctrineAssert\UniqueEntity("name")
 */
class Group extends AbstractRoleSubject implements OrderableInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"},
     *     mappedBy="groups"
     * )
     */
    protected $users;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"},
     *     inversedBy="groups"
     * )
     * @ORM\JoinTable(name="claro_group_role")
     */
    protected $roles;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocSession",
     *     cascade={"persist"},
     *     mappedBy="groups"
     * )
     */
    protected $moocSessions;

    public function __construct()
    {
        parent::__construct();
        $this->users 			 = new ArrayCollection();
        $this->moocSessions      = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addUser(User $user)
    {
        $user->getGroups()->add($this);
    }

    public function removeUser(User $user)
    {
        $user->getGroups()->removeElement($this);
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getUserIds()
    {
        $users = $this->getUsers();
        $userIds = array();
        foreach ($users as $user) {
            array_push($userIds, $user->getId());
        }

        return $userIds;
    }

    public function getPlatformRole()
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                return $role;
            }
        }
    }

    public function setPlatformRole($platformRole)
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                $removedRole = $role;
            }
        }

        if (isset($removedRole)) {
            $this->roles->removeElement($removedRole);
        }

        $this->roles->add($platformRole);
    }

    public function containsUser(User $user)
    {
        return $this->users->contains($user);
    }

    public function getOrderableFields()
    {
        return array('name', 'id');
    }

    public function getMoocSessions()
    {
    	return $this->moocSessions;
    }
    
    public function setMoocSessions(ArrayCollection $moocSessions)
    {
    	$this->moocSessions = $moocSessions;
    }
}
