<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Pager\PagerFactory;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Monolog\Logger;

/**
 * @DI\Service("claroline.manager.group_manager")
 */
class GroupManager
{
    private $om;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var UserRepository */
    private $userRepo;
    private $pagerFactory;
    private $translator;
    private $eventDispatcher;
    private $logger;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "translator"      = @DI\Inject("translator"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "logger"			 = @DI\Inject("logger")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        Translator $translator,
        StrictDispatcher $eventDispatcher,
    	Logger $logger
    )
    {
        $this->om = $om;
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->pagerFactory = $pagerFactory;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * Persists and flush a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     */
    public function insertGroup(Group $group)
    {
        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * Removes a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     */
    public function deleteGroup(Group $group)
    {
        $this->om->remove($group);
        $this->om->flush();
    }

    /**
     * @todo what does this method do ?
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param string                             $oldPlatformRoleTransactionKey
     */
    public function updateGroup(Group $group, $oldPlatformRoleTransactionKey)
    {
        $unitOfWork = $this->om->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($group);
        $newPlatformRoleTransactionKey = $group->getPlatformRole()->getTranslationKey();

        if ($oldPlatformRoleTransactionKey !== $newPlatformRoleTransactionKey) {
            $changeSet['platformRole'] = array($oldPlatformRoleTransactionKey, $newPlatformRoleTransactionKey);
        }
        $this->eventDispatcher->dispatch('log', 'Log\LogGroupUpdate', array($group, $changeSet));

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * Adds an array of user to a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param User[]                             $users
     */
    public function addUsersToGroup(Group $group, array $users, $checkNotInGroup = true)
    {
    	$this->om->startFlushSuite();
        foreach ($users as $i => $user) {
            if (!$checkNotInGroup || !$group->containsUser($user)) {
            	$this->logger->info("Start adding user to group...");
                $group->addUser($user);
                $this->eventDispatcher->dispatch('log', 'Log\LogGroupAddUser', array($group, $user));
                $this->logger->info("User added to group !");
            } else {
            	$this->logger->info("User already in group.");
            }
            unset($users[$i]);
        }

        $this->om->persist($group);
        
        $this->om->endFlushSuite();
    }

    /**
     * Removes an array of user from a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param User[]                             $users
     */
    public function removeUsersFromGroup(Group $group, array $users)
    {
        foreach ($users as $user) {
            $group->removeUser($user);
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param array                              $users
     *
     * @return array
     */
    public function importUsers(Group $group, array $users)
    {
        $toImport = array();
        $nonImportedUsers = array();
        
        $usernamesAndMails = $this->getUsernamesAndMailsFromCSV($users);
		$toImport = $this->userRepo->findByUsernameInNotInGroup($usernamesAndMails['usernames'], $group);
        
		if (count($toImport) > 0) {
			echo "Adding ".count($toImport)." to the group...";
        	$this->addUsersToGroup($group, $toImport, false);
		} else {
			echo "Nobody found to add to the group...";
		}
		echo "\n";

        unset($toImport);
        $this->om->clear();
        return $nonImportedUsers;
    }
    
    public function getUsernamesAndMailsFromCSV($array) {
    	$usernames = array();
    	$mails = array();
    	foreach ($array as $userLine) {
    		$usernames[] = $userLine[2];
    		$mails[] = $userLine[4];
    	}
    	
    	return array('usernames' => $usernames, 'mails' => $mails);
    }

    /**
     * Serialize a group array.
     *
     * @param Group[] $groups
     *
     * @return array
     */
    public function convertGroupsToArray(array $groups)
    {
        $content = array();
        $i = 0;

        foreach ($groups as $group) {
            $content[$i]['id'] = $group->getId();
            $content[$i]['name'] = $group->getName();

            $rolesString = '';
            $roles = $group->getEntityRoles();
            $rolesCount = count($roles);
            $j = 0;

            foreach ($roles as $role) {
                $rolesString .= "{$this->translator->trans($role->getTranslationKey(), array(), 'platform')}";

                if ($j < $rolesCount - 1) {
                    $rolesString .= ' ,';
                }
                $j++;
            }
            $content[$i]['roles'] = $rolesString;
            $i++;
        }

        return $content;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer                                                  $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspaceOutsiders(AbstractWorkspace $workspace, $page, $max = 50)
    {
        $query = $this->groupRepo->findWorkspaceOutsiders($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string                                                   $search
     * @param integer                                                  $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $page, $max = 50)
    {
        $query = $this->groupRepo->findWorkspaceOutsidersByName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer                                                  $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByWorkspace(AbstractWorkspace $workspace, $page, $max = 50)
    {
        $query = $this->groupRepo->findByWorkspace($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[] $workspaces
     *
     * @return Group[]
     */
    public function getGroupsByWorkspaces(array $workspaces)
    {
        return $this->groupRepo->findGroupsByWorkspaces($workspaces, true);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[] $workspaces
     * @param string                                                     $search
     *
     * @return Group[]
     */
    public function getGroupsByWorkspacesAndSearch(array $workspaces, $search)
    {
        return $this->groupRepo->findGroupsByWorkspacesAndSearch(
            $workspaces,
            $search
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string                                                   $search
     * @param integer                                                  $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByWorkspaceAndName(AbstractWorkspace $workspace, $search, $page, $max = 50)
    {
        $query = $this->groupRepo->findByWorkspaceAndName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroups($page, $max = 50, $orderedBy = 'id', $order = null)
    {
        $order = $order === 'DESC' ? 'DESC' : 'ASC';
        $query = $this->groupRepo->findAll(false, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByName($search, $page, $max = 50, $orderedBy = 'id')
    {
        $query = $this->groupRepo->findByName($search, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     * @param integer                             $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByRoles(array $roles, $page = 1, $max = 50, $orderedBy = 'id', $order = null)
    {
        $query = $this->groupRepo->findByRoles($roles, true, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[]                      $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer                                                  $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOutsidersByWorkspaceRoles(array $roles, AbstractWorkspace $workspace, $page = 1, $max = 50)
    {
        $query = $this->groupRepo->findOutsidersByWorkspaceRoles($roles, $workspace, true);

        return  $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     * @param string                              $name
     * @param integer                             $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByRolesAndName(array $roles, $name, $page = 1, $max = 50, $orderedBy = 'id')
    {
        $query = $this->groupRepo->findByRolesAndName($roles, $name, true, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[]                      $roles
     * @param string                                                   $name
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer                                                  $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOutsidersByWorkspaceRolesAndName(
        array $roles,
        $name,
        AbstractWorkspace $workspace,
        $page = 1,
        $max = 50
    )
    {
        $query = $this->groupRepo->findOutsidersByWorkspaceRolesAndName($roles, $name, $workspace, true);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param integer $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getAllGroups($page, $max = 50)
    {
        $query = $this->groupRepo->findAll(false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param integer $page
     * @param string  $search
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getAllGroupsBySearch($page, $search, $max = 50)
    {
        $query = $this->groupRepo->findAllGroupsBySearch($search);

        return $this->pagerFactory->createPagerFromArray($query, $page, $max);
    }

    /**
     * @param string[] $names
     *
     * @return Group[]
     */
    public function getGroupsByNames(array $names)
    {
        if (count($names) > 0) {
            return $this->groupRepo->findGroupsByNames($names);
        }

        return array();
    }
    
    public function getGroupById($groupId, $getRelations = true) {
    	if ($getRelations) {
    		return $this->groupRepo->findOneById($groupId);
    	} else {
    		return $this->groupRepo->findOneByIdWithoutRelations($groupId);
    	}
    }
}
