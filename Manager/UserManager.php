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
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\UserPublicProfilePreferences;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;

/**
 * @DI\Service("claroline.manager.user_manager")
 */
class UserManager
{
    private $platformConfigHandler;
    private $strictEventDispatcher;
    private $mailManager;
    private $objectManager;
    private $pagerFactory;
    private $personalWsTemplateFile;
    private $roleManager;
    private $toolManager;
    private $translator;
    private $userRepo;
    private $validator;
    private $workspaceManager;
    private $em;
    private $logger;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "personalWsTemplateFile" = @DI\Inject("%claroline.param.templates_directory%"),
     *     "mailManager"            = @DI\Inject("claroline.manager.mail_manager"),
     *     "objectManager"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"           = @DI\Inject("claroline.pager.pager_factory"),
     *     "platformConfigHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "strictEventDispatcher"  = @DI\Inject("claroline.event.event_dispatcher"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"             = @DI\Inject("translator"),
     *     "validator"              = @DI\Inject("validator"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "em"		   				= @DI\Inject("doctrine.orm.entity_manager"),
     *     "logger"					= @DI\Inject("logger")
     * })
     */
    public function __construct(
        $personalWsTemplateFile,
        MailManager $mailManager,
        ObjectManager $objectManager,
        PagerFactory $pagerFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        RoleManager $roleManager,
        StrictDispatcher $strictEventDispatcher,
        ToolManager $toolManager,
        Translator $translator,
        ValidatorInterface $validator,
        WorkspaceManager $workspaceManager,
    	EntityManager $em,
    	Logger $logger
    )
    {
        $this->userRepo               = $objectManager->getRepository('ClarolineCoreBundle:User');
        $this->roleManager            = $roleManager;
        $this->workspaceManager       = $workspaceManager;
        $this->toolManager            = $toolManager;
        $this->strictEventDispatcher  = $strictEventDispatcher;
        $this->personalWsTemplateFile = $personalWsTemplateFile . "default.zip";
        $this->translator             = $translator;
        $this->platformConfigHandler  = $platformConfigHandler;
        $this->pagerFactory           = $pagerFactory;
        $this->objectManager          = $objectManager;
        $this->mailManager            = $mailManager;
        $this->validator              = $validator;
        $this->em					  = $em;
        $this->logger 				  = $logger;
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function createUser(User $user)
    {
        $this->objectManager->startFlushSuite();
        $this->setPersonalWorkspace($user);

        $user
            ->setPublicUrl($this->generatePublicUrl($user))
            ->setPublicProfilePreferences(new UserPublicProfilePreferences());

        $this->toolManager->addRequiredToolsToUser($user);
        $this->roleManager->setRoleToRoleSubject($user, PlatformRoles::USER);
        $this->objectManager->persist($user);
        $this->strictEventDispatcher->dispatch('log', 'Log\LogUserCreate', array($user));
        $this->objectManager->endFlushSuite();

        /*if ($this->mailManager->isMailerAvailable()) {
            $this->mailManager->sendCreationMessage($user);
        }*/

        return $user;
    }

    /**
     * Send an email validation
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function sendEmailValidation($user, $moocId = null){
        if ($this->mailManager->isMailerAvailable()) {
            $this->mailManager->sendValidationMessage($user, $moocId);
        }
    }

    /**
     * Send an email post-validation
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function sendEmailValidationConfirmee($user){
    	if ($this->mailManager->isMailerAvailable()) {
    		$this->mailManager->sendValidationConfirmeeMessage($user);
            $this->mailManager->sendCreationMessage($user);
    	}
    }

    /**
     * Send an email post-changing password
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function sendEmailChangePassword($user){
        if ($this->mailManager->isMailerAvailable()) {
            $this->mailManager->sendChangePasswordMessage($user);
        }
    }


    /**
     * Rename a user.
     *
     * @param User $user
     * @param $username
     */
    public function rename(User $user, $username)
    {
        $user->setUsername($username);
        $personalWorkspaceName = $this->translator->trans('personal_workspace', array(), 'platform') . $user->getUsername();
        $pws = $user->getPersonalWorkspace();
        if ( $pws ) {
            $this->workspaceManager->rename($pws, $personalWorkspaceName);
        }
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function setIsMailNotified(User $user, $isNotified)
    {
        $user->setIsMailNotified($isNotified);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * Removes a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function deleteUser(User $user)
    {

        $unique = md5( uniqid() );
        $identifier = 'invite-'. substr($unique, 0, 8);
        $password = 'Invite#' . $unique;
        //soft delete~
        $user->setMail($identifier . '@solerni.org');
        $user->setFirstName('Invité');
        $user->setLastName('Invite');
        $user->setPlainPassword($password);
        $user->setUsername($identifier);
        $user->setUsername($identifier);
        $user->setPublicUrl($identifier);
        $user->setIsEnabled(false);

        // keeping the user's workspace with its original code
        // would prevent creating a user with the same username
        // todo: workspace deletion should be an option
        $ws = $user->getPersonalWorkspace();

        if ($ws) {
            $ws->setCode($ws->getCode() . '#deleted_user#' . $user->getId());
            $ws->setPublic(false);
            $ws->setDisplayable(false);
            $this->objectManager->persist($ws);
        }

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->strictEventDispatcher->dispatch('log', 'Log\LogUserDelete', array($user));
        $this->strictEventDispatcher->dispatch('delete_user', 'DeleteUser', array($user));
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     * This user will have the additional role  $roleName.
     * $roleName must already exists.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string                            $roleName
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function createUserWithRole(User $user, $roleName)
    {
        $this->objectManager->startFlushSuite();
        $this->createUser($user);

        $this->roleManager->setRoleToRoleSubject($user, $roleName);
        $this->objectManager->endFlushSuite();

        return $user;
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     * This user will have the additional roles $roles.
     * These roles must already exists.
     *
     * @param \Claroline\CoreBundle\Entity\User            $user
     * @param \Doctrine\Common\Collections\ArrayCollection $roles
     */
    public function insertUserWithRoles(User $user, ArrayCollection $roles)
    {
        $this->objectManager->startFlushSuite();
        $this->createUser($user);
        $this->roleManager->associateRoles($user, $roles);
        $this->objectManager->endFlushSuite();
    }

    /**
     * Import users from an array.
     * There is the array format:
     * @todo some batch processing
     *
     * array(
     *     array(firstname, lastname, username, pwd, email, code, phone),
     *     array(firstname2, lastname2, username2, pwd2, email2, code2, phone2),
     *     array(firstname3, lastname3, username3, pwd3, email3, code3, phone3),
     * )
     *
     * @param array $users
     *
     * @return array
     */
    public function importUsers(array $users)
    {

    	$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
    	gc_enable();
    	$config = Configuration::fromTemplate($this->personalWsTemplateFile);

    	$this->objectManager->beginTransaction();
        $this->objectManager->startFlushSuite();

        foreach ($users as $i => $user) {
            $newUser = new User();
            $newUser->setFirstName($user[0]);
            $newUser->setLastName($user[1]);
            $newUser->setUsername($user[2]);
            $newUser->setPlainPassword($user[3]);
            $newUser->setMail($user[4]);
            if (isset($user[5])) {
            	$newUser->setAdministrativeCode($user[5]);
            }
            if (isset($user[6])) {
            	$newUser->setPhone($user[6]);
            }
            $this->createUserForImport($newUser, $config);
            echo "\rUser nb. ".$i;
        }

        $this->objectManager->endFlushSuite();
        $this->objectManager->commit();
        $this->objectManager->clear();
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function createUserForImport(User $user, $config) {

    	$this->setPersonalWorkspaceForImport($user, $config);

    	$user
	    	->setPublicUrl($user->getUsername())
	    	->setPublicProfilePreferences(new UserPublicProfilePreferences());

    	$this->toolManager->addRequiredToolsToUser($user);
    	$this->roleManager->setRoleToRoleSubject($user, PlatformRoles::USER);
    	$this->objectManager->persist($user);
    	$this->strictEventDispatcher->dispatch('log', 'Log\LogUserCreate', array($user));

    	return $user;
    }
    /**
     * Creates the personal workspace of a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function setPersonalWorkspaceForImport(User $user, $config)
    {
        $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
        $locale = $this->platformConfigHandler->getParameter('locale_language');
        $this->translator->setLocale($locale);
        $personalWorkspaceName = $this->translator->trans('personal_workspace', array(), 'platform') . $user->getUsername();
        $config->setWorkspaceName($personalWorkspaceName);
        $config->setWorkspaceCode($user->getUsername());
        $workspace = $this->workspaceManager->create($config, $user, true);
        $user->setPersonalWorkspace($workspace);
    }

    /**
     * Creates the personal workspace of a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function setPersonalWorkspace(User $user)
    {
        $config = Configuration::fromTemplate($this->personalWsTemplateFile);
        $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
        $locale = $this->platformConfigHandler->getParameter('locale_language');
        $this->translator->setLocale($locale);
        $personalWorkspaceName = $this->translator->trans('personal_workspace', array(), 'platform') . $user->getUsername();
        $config->setWorkspaceName($personalWorkspaceName);
        $config->setWorkspaceCode($user->getUsername() . '-' . uniqid() );
        $workspace = $this->workspaceManager->create($config, $user);
        $user->setPersonalWorkspace($workspace);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * Sets an array of platform role to a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param ArrayCollection                   $roles
     */
    public function setPlatformRoles(User $user, ArrayCollection $roles)
    {
        $user->setPlatformRoles($roles);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * Serialize a user.
     *
     * @param array $users
     *
     * @return array
     */
    public function convertUsersToArray(array $users)
    {
        $content = array();
        $i = 0;

        foreach ($users as $user) {
            $content[$i]['id'] = $user->getId();
            $content[$i]['username'] = $user->getUsername();
            $content[$i]['lastname'] = $user->getLastName();
            $content[$i]['firstname'] = $user->getFirstName();
            $content[$i]['administrativeCode'] = $user->getAdministrativeCode();

            $rolesString = '';
            $roles = $user->getEntityRoles();
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
     * @param type $username
     *
     * @return User
     */
    public function getUserByUsername($username)
    {
        try {
            $user = $this->userRepo->loadUserByUsername($username);
        } catch (\Exception $e)
        {
            $user = null;
        }
        return $user;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->userRepo->refreshUser($user);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role                        $role
     *
     * @return User[]
     */
    public function getUserByWorkspaceAndRole(AbstractWorkspace $workspace, Role $role)
    {
        return $this->userRepo->findByWorkspaceAndRole($workspace, $role);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string                                                   $search
     * @param integer                                                  $page
     * @param integer                                                  $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $page, $max = 20)
    {
        $query = $this->userRepo->findWorkspaceOutsidersByName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer                                                  $page
     * @param integer                                                  $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getWorkspaceOutsiders(AbstractWorkspace $workspace, $page, $max = 20)
    {
        $query = $this->userRepo->findWorkspaceOutsiders($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getAllUsers($page, $max = 20, $orderedBy = 'id', $order = null)
    {
        $query = $this->userRepo->findAll(false, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max, true, false);
    }

    /**
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getAllUsersBySearch($page, $search, $max = 20, $orderedBy = 'id', $order = null)
    {
        $users = $this->userRepo->findAllUserBySearch($search, $orderedBy, $order);

        return $this->pagerFactory->createPagerFromArray($users, $page, $max, true, false);
    }

    /**
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getUsersByName($search, $page, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findByName($search, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer                            $page
     * @param integer                            $max
     * @param string                             $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getUsersByGroup(Group $group, $page, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findByGroup($group, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     *
     * @return User[]
     */
    public function getUsersByGroupWithoutPager(Group $group)
    {
        return $this->userRepo->findByGroup($group);
    }

    /**
     * @param AbstractWorkspace $workspace
     *
     * @return User[]
     */
    public function getByWorkspaceWithUsersFromGroup(AbstractWorkspace $workspace)
    {
        return $this->userRepo->findByWorkspaceWithUsersFromGroup($workspace);
    }

    /**
     *
     * @param string                             $search
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer                            $page
     * @param integer                            $max
     * @param string                             $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByNameAndGroup($search, Group $group, $page, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findByNameAndGroup($search, $group, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer                                                  $page
     * @param integer                                                  $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspace(AbstractWorkspace $workspace, $page, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findByWorkspace($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[] $workspaces
     * @param integer                                                    $page
     * @param integer                                                    $max
     * @param string                                                     $orderedBy
     * @param string                                                     $order
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspaces(array $workspaces, $page, $max = 20, $orderedBy = 'id', $order = null)
    {
        $query = $this->userRepo->findUsersByWorkspaces($workspaces, false, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max, true, false);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[] $workspaces
     * @param integer                                                    $page
     * @param string                                                     $search
     * @param integer                                                    $max
     * @param string                                                     $orderedBy
     * @param string                                                     $order
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspacesAndSearch(
        array $workspaces,
        $page,
        $search,
        $max = 20,
        $orderedBy = 'id',
        $order = null
    )
    {
        $users = $this->userRepo
            ->findUsersByWorkspacesAndSearch($workspaces, $search, $orderedBy, $order);

        return $this->pagerFactory->createPagerFromArray($users, $page, $max, true, false);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string                                                   $search
     * @param integer                                                  $page
     * @param integer                                                  $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspaceAndName(AbstractWorkspace $workspace, $search, $page, $max = 20)
    {
        $query = $this->userRepo->findByWorkspaceAndName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string                                                   $search
     * @param integer                                                  $page
     * @param integer                                                  $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getAllUsersByWorkspaceAndName(AbstractWorkspace $workspace, $search, $page, $max = 20)
    {
        $query = $this->userRepo->findAllByWorkspaceAndName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer                            $page
     * @param integer                            $max
     * @param string                             $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getGroupOutsiders(Group $group, $page, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findGroupOutsiders($group, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param integer                            $page
     * @param string                             $search
     * @param integer                            $max
     * @param string                             $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getGroupOutsidersByName(Group $group, $page, $search, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findGroupOutsidersByName($group, $search, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $excludedUser
     *
     * @return User[]
     */
    public function getAllUsersExcept(User $excludedUser)
    {
        return $this->userRepo->findAllExcept($excludedUser);
    }

    /**
     * @param string[] $usernames
     *
     * @return User[]
     */
    public function getUsersByUsernames(array $usernames)
    {
        return $this->userRepo->findByUsernames($usernames);
    }

    /**
     * @return integer
     */
    public function getNbUsers()
    {
        return $this->userRepo->count();
    }

    public function countUsersForPlatformRoles()
    {
        $roles = $this->roleManager->getAllPlatformRoles();
        $usersInRoles = array();
        $usersInRoles['user_accounts'] = 0;
        foreach ($roles as $role) {
            $restrictionRoleNames = null;
            if ($role->getName() === 'ROLE_USER') {
                $restrictionRoleNames = array('ROLE_WS_CREATOR', 'ROLE_ADMIN');
            } elseif ($role->getName() === 'ROLE_WS_CREATOR') {
                $restrictionRoleNames = array('ROLE_ADMIN');
            }
            $usersInRoles[$role->getTranslationKey()] = intval(
                $this->userRepo->countUsersByRole($role, $restrictionRoleNames)
            );
            $usersInRoles['user_accounts'] += $usersInRoles[$role->getTranslationKey()];
        }

        return $usersInRoles;
    }

    /**
     * @param integer[] $ids
     *
     * @return User[]
     */
    public function getUsersByIds(array $ids)
    {
        return $this->objectManager->findByIds('Claroline\CoreBundle\Entity\User', $ids);
    }

    /**
     * @param integer $max
     *
     * @return User[]
     */
    public function getUsersEnrolledInMostWorkspaces($max)
    {
        return $this->userRepo->findUsersEnrolledInMostWorkspaces($max);
    }

    /**
     * @param integer $max
     *
     * @return User[]
     */
    public function getUsersOwnersOfMostWorkspaces($max)
    {
        return $this->userRepo->findUsersOwnersOfMostWorkspaces($max);
    }

    /**
     * @param integer $userId
     *
     * @return User
     */
    public function getUserById($userId)
    {
        return $this->userRepo->find($userId);
    }

    /**
     * @param Role[]  $roles
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getByRolesIncludingGroups(array $roles, $page = 1, $max = 20, $orderedBy = 'id', $order= null)
    {
        $res = $this->userRepo->findByRolesIncludingGroups($roles, true, $orderedBy, $order);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param AbstractWorkspace $workspace
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getNotifiedByWorkspace(AbstractWorkspace $workspace, $page = 1, $max = 20, $orderedBy = 'id', $order= null)
    {
    	$res = $this->userRepo->findNotifiedByWorkspace($workspace, true, $orderedBy, $order);

    	return $this->pagerFactory->createPager($res, $page, $max);
    }


    /**
     * @param AbstractWorkspace $workspace
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getNotifiedByWorkspaceWithName(AbstractWorkspace $workspace, $search, $page = 1, $max = 20, $orderedBy = 'id', $order= null)
    {
    	$res = $this->userRepo->findNotifiedByWorkspaceWithName($workspace, $search, true, $orderedBy, $order);

    	return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[]  $roles
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getByRolesAndNameIncludingGroups(array $roles, $search, $page = 1, $max = 20, $orderedBy = 'id', $direction = null)
    {
        $res = $this->userRepo->findByRolesAndNameIncludingGroups($roles, $search, true, $orderedBy, $direction);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[]  $roles
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByRoles(array $roles, $page = 1, $max = 20)
    {
        $res = $this->userRepo->findByRoles($roles, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[]                                                   $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer                                                  $page
     * @param integer                                                  $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getOutsidersByWorkspaceRoles(array $roles, AbstractWorkspace $workspace, $page = 1, $max = 20)
    {
        $res = $this->userRepo->findOutsidersByWorkspaceRoles($roles, $workspace, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[]  $roles
     * @param string  $name
     * @param integer $page
     * @param integer $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByRolesAndName(array $roles, $name, $page = 1, $max  = 20)
    {
        $res = $this->userRepo->findByRolesAndName($roles, $name, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[]                                                   $roles
     * @param string                                                   $name
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer                                                  $page
     * @param integer                                                  $max
     *
     * @return \Pagerfanta\Pagerfanta| \Doctrine\ORM\Query
     */
    public function getOutsidersByWorkspaceRolesAndName(
        array $roles, $name, AbstractWorkspace $workspace, $page = 1, $max = 20
    )
    {
        $res = $this->userRepo->findOutsidersByWorkspaceRolesAndName($roles, $name, $workspace, true);

        return ($page !== 0) ? $this->pagerFactory->createPager($res, $page, $max): $res;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function getUserByEmail($email)
    {
        return $this->userRepo->findOneByMail($email);
    }

    /**
     * @todo Please describe me. I couldn't find findOneByResetPasswordHash.
     *
     * @param string $resetPassword
     *
     * @return User
     */
    public function getResetPasswordHash($resetPassword)
    {
        return $this->userRepo->findOneByResetPasswordHash($resetPassword);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function uploadAvatar(User $user)
    {
        if (null !== $user->getPictureFile()) {
        	$now = time();
            $user->setPicture(
                sha1($user->getPictureFile()->getClientOriginalName().
                	'-'.$user->getId().
                	'-'.$now).'.'.$user->getPictureFile()->guessExtension()
            );
            $user->getPictureFile()->move(__DIR__.'/../../../../../../web/uploads/pictures', $user->getPicture());
        }
    }

    /**
     * Set the user locale.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param String                            $locale Language with format en, fr, es, etc.
     */
    public function setLocale(User $user, $locale = 'en')
    {
        $user->setLocale($locale);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function toArrayForPicker($users)
    {
        $resultArray = array();

        $resultArray['users'] = array();
        if (count($users)>0) {
            foreach ($users as $user) {
                $userArray = array();
                $userArray['id'] = $user->getId();
                $userArray['name'] = $user->getFirstName()." ".$user->getLastName();
                $userArray['mail'] = $user->getMail();
                $userArray['avatar'] = $user->getPicture();
                array_push($resultArray['users'], $userArray);
            }
        }

        return $resultArray;
    }

    /**
     * @param User $user
     * @param int  $try
     *
     * @return string
     */
    public function generatePublicUrl(User $user, $try = 0)
    {
        $firstNameClean = preg_replace(USER::$patternReplaceUrlPublic , '_' , $user->getFirstName());
        $lastNameClean = preg_replace(USER::$patternReplaceUrlPublic , '_' , $user->getLastName());

        $publicUrl = strtolower(sprintf('%s.%s', $firstNameClean, $lastNameClean));

        if (0 < $try) {
            $publicUrl .= $try;
        }

        $searchedUsers = $this->objectManager->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl($publicUrl);
        if (null !== $searchedUsers) {
            $publicUrl = $this->generatePublicUrl($user, ++$try);
        }

        return $publicUrl;
    }

    /**
     * @return UserPublicProfilePreferences
     */
    public function getUserPublicProfilePreferencesForAdmin()
    {
        $userPublicProfilePreferences = new UserPublicProfilePreferences();
        $userPublicProfilePreferences
            ->setSharePolicy(UserPublicProfilePreferences::SHARE_POLICY_EVERYBODY)
            ->setAllowMailSending(true)
            ->setAllowMessageSending(true)
            ->setDisplayEmail(true)
            ->setDisplayPhoneNumber(true);

        return $userPublicProfilePreferences;
    }

    public function getWorkspaceUserIds(AbstractWorkspace $workspace, array $excludeRoles) {
    	$ids = $this->userRepo->getWorkspaceUserIds($workspace, $excludeRoles);

    	$ids = array_unique(array_map('current', $ids));
    	return $ids;
    }
}