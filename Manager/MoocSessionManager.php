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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\UnknownToolException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use Claroline\CoreBundle\Entity\Group;

/**
 * @DI\Service("claroline.manager.mooc_session_manager")
 */
class MoocSessionManager
{
    /** @var HomeTabManager */
    private $homeTabManager;
    /** @var MaskManager */
    private $maskManager;
    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    /** @var RoleManager */
    private $roleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var ResourceNodeRepository */
    private $resourceRepo;
    /** @var ResourceRightsRepository */
    private $resourceRightsRepo;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var WorkspaceRepository */
    private $userRepo;
    /** @var UserRepository */
    private $workspaceRepo;
    /** @var ToolManager */
    private $toolManager;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var ClaroUtilities */
    private $ut;
    /** @var string */
    private $templateDir;
    /** @var PagerFactory */
    private $pagerFactory;
    private $workspaceFavouriteRepo;
    private $container;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "homeTabManager"  = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "ut"              = @DI\Inject("claroline.utilities.misc"),
     *     "templateDir"     = @DI\Inject("%claroline.param.templates_directory%"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "container"       = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        HomeTabManager $homeTabManager,
        RoleManager $roleManager,
        MaskManager $maskManager,
        ResourceManager $resourceManager,
        ToolManager $toolManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ClaroUtilities $ut,
        $templateDir,
        PagerFactory $pagerFactory,
        ContainerInterface $container)
    {
        $this->homeTabManager = $homeTabManager;
        $this->maskManager = $maskManager;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->toolManager = $toolManager;
        $this->ut = $ut;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->templateDir = $templateDir;
        $this->resourceTypeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->orderedToolRepo = $om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->resourceRightsRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->workspaceFavouriteRepo = $om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceFavourite');
        $this->pagerFactory = $pagerFactory;
        $this->container = $container;
    }


    public function addGroupsToSession(array $groups, array $sessions) {
    	foreach ($sessions as $session) {
    		foreach ($groups as $group) {
    			/* @var $session MoocSession */
    			if (!$session->getGroups()->contains($group)) {
    				$session->getGroups()->add($group);
    			}
    			$this->om->persist($session);
    		}
    	}
    	$this->om->flush();
    }
    

    public function addUsersToSession(array $users, array $sessions) {
    	foreach ($sessions as $session) {
    		foreach ($users as $user) {
    			/* @var $session MoocSession */
    			if (!$session->getUsers()->contains($user)) {
    				$session->getUsers()->add($user);
    			}
    			$this->om->persist($session);
    		}
    	}
    	$this->om->flush();
    }
    
    /**
     * Remove a group from a session
     * @param Group $group The group
     * @param MoocSession $session The session
     */
    public function removeGroupFromSession(Group $group, MoocSession $session) {
    	$sessionGroups = $session->getGroups();
    	foreach ($sessionGroups as $i => $sessionGroup) {
    		if ($sessionGroup->getId() == $group->getId()) {
    			$sessionGroups->remove($i);
    			break;
    		}
    	}
    	$this->om->persist($session);
    	$this->om->flush();
    }
    

    /**
     * Remove a user from a session
     * @param User $user The user
     * @param MoocSession $session The session
     */
    public function removeUserFromSession(User $user, MoocSession $session) {
    	$sessionUsers = $session->getUsers();
    	foreach ($sessionUsers as $i => $sessionUser) {
    		if ($sessionUser->getId() == $user->getId()) {
    			$sessionUsers->remove($i);
    			break;
    		}
    	}
    	$this->om->persist($session);
    	$this->om->flush();
    }
}
