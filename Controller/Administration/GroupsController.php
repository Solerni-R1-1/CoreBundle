<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Form\ImportUserType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

//This class belongs to the Users admin tool.
class GroupsController extends Controller
{
    private $userManager;
    private $roleManager;
    private $groupManager;
    private $eventDispatcher;
    private $formFactory;
    private $request;
    private $router;
    private $toolManager;
    private $sc;
    private $userAdminTool;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "groupManager"       = @DI\Inject("claroline.manager.group_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "sc"                 = @DI\Inject("security.context"),
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        GroupManager $groupManager,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        Request $request,
        RouterInterface $router,
        SecurityContextInterface $sc,
        ToolManager $toolManager,
    	TranslatorInterface $translator
    )
    {
        $this->userManager		= $userManager;
        $this->roleManager		= $roleManager;
        $this->groupManager		= $groupManager;
        $this->eventDispatcher	= $eventDispatcher;
        $this->formFactory		= $formFactory;
        $this->request			= $request;
        $this->router			= $router;
        $this->toolManager		= $toolManager;
        $this->userAdminTool	= $this->toolManager->getAdminToolByName('user_management');
        $this->sc				= $sc;
        $this->translator       = $translator;
    }

    /**
     * @EXT\Route("/new", name="claro_admin_group_creation_form")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creationFormAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP);

        return array('form_group' => $form->createView());
    }

    /**
     * @EXT\Route("/", name="claro_admin_create_group")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration/Groups:creationForm.html.twig")
     *
     * Creates a group and redirects to the group list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP, array());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $group = $form->getData();
            $userRole = $this->roleManager->getRoleByName('ROLE_USER');
            $group->setPlatformRole($userRole);
            $this->groupManager->insertGroup($group);
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupCreate', array($group));

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return array('form_group' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/page/{page}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_admin_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id", "direction"="ASC"}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_admin_group_list_search",
     *     defaults={"page"=1, "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template()
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\Group",
     *     options={"orderable"=true}
     * )
     *
     * Returns the platform group list.
     *
     * @param integer $page
     * @param string  $search
     * @param integer $max
     * @param string  $order
     *
     * @return array
     */
    public function listAction($page, $search, $max, $order, $direction)
    {
        $this->checkOpen();
        $pager = $search === '' ?
            $this->groupManager->getGroups($page, $max, $order, $direction) :
            $this->groupManager->getGroupsByName($search, $page, $max, $order, $direction);
        $direction = $direction === 'DESC' ? 'ASC' : 'DESC';

        return array('pager' => $pager, 'search' => $search, 'max' => $max, 'order' => $order, 'direction' => $direction);
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_multidelete_group",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true}
     * )
     *
     * Deletes multiple groups.
     *
     * @param Group[] $groups
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(array $groups)
    {
        $this->checkOpen();

        foreach ($groups as $group) {
            $this->groupManager->deleteGroup($group);
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupDelete', array($group));
        }

        return new Response('groups removed', 204);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}/users/page/{page}/max/{max}/order/{order}",
     *     name="claro_admin_user_of_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id"}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/{groupId}/users/page/{page}/search/{search}/max/{max}/{order}",
     *     name="claro_admin_user_of_group_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1, "max"=50, "order"="id"}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     *
     * Returns the users of a group.
     *
     * @param Group   $group
     * @param integer $page
     * @param string  $search
     * @param integer $max
     * @param string  $order
     *
     * @return array
     */
    public function listMembersAction(Group $group, $page, $search, $max, $order)
    {
        $this->checkOpen();

        $pager = $search === '' ?
            $this->userManager->getUsersByGroup($group, $page, $max, $order) :
            $this->userManager->getUsersByNameAndGroup($search, $group, $page, $max, $order);

        return array('pager' => $pager, 'search' => $search, 'group' => $group, 'max' => $max, 'order' => $order);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}/add-users/page/{page}/max/{max}/order/{order}",
     *     name="claro_admin_outside_of_group_user_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id"}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/{groupId}/add-users/page/{page}/search/{search}/max/{max}/order/{order}",
     *     name="claro_admin_outside_of_group_user_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1, "max"=50, "order"="id"}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     *
     * Displays the user list with a control allowing to add them to a group.
     *
     * @param Group   $group
     * @param integer $page
     * @param string  $search
     * @param integer $max
     * @param string  $order
     *
     * @return array
     */
    public function listNonMembersAction(Group $group, $page, $search, $max, $order)
    {
        $this->checkOpen();

        $pager = $search === '' ?
            $this->userManager->getGroupOutsiders($group, $page, $max, $order) :
            $this->userManager->getGroupOutsidersByName($group, $page, $search, $max, $order);

        return array('pager' => $pager, 'search' => $search, 'group' => $group, 'max' => $max, 'order' => $order);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}/users",
     *     name="claro_admin_multiadd_user_to_group",
     *     requirements={"groupId"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     *
     * Adds multiple users to a group.
     *
     * @param Group     $group
     * @param User[]    $users
     *
     * @return Response
     */
    public function addMembersAction(Group $group, array $users)
    {
        $this->checkOpen();
        $this->groupManager->addUsersToGroup($group, $users);

        foreach ($users as $user) {
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupAddUser', array($group, $user));
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}/users",
     *     name="claro_admin_multidelete_user_from_group",
     *     options={"expose"=true},
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     *
     * Removes users from a group.
     *
     * @param Group  $group
     * @param User[] $users
     *
     * @return Response
     */
    public function removeMembersAction(Group $group, array $users)
    {
        $this->checkOpen();
        $this->groupManager->removeUsersFromGroup($group, $users);

        foreach ($users as $user) {
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupRemoveUser', array($group, $user));
        }

        return new Response('User(s) removed', 204);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}",
     *     name="claro_admin_group_settings_form",
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template
     *
     * Displays an edition form for a group.
     *
     * @param Group $group
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingsFormAction(Group $group)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP_SETTINGS, array(), $group);

        return array(
            'group' => $group,
            'form_settings' => $form->createView()
        );
    }

    /**
     * @EXT\Route("/{groupId}", name="claro_admin_update_group_settings")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration/Groups:settingsForm.html.twig")
     *
     * Updates the settings of a group and redirects to the group list.
     *
     * @param Group $group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateSettingsAction(Group $group)
    {
        $this->checkOpen();
        $oldPlatformRoleTransactionKey = $group->getPlatformRole()->getTranslationKey();
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP_SETTINGS, array(), $group);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $group = $form->getData();
            $this->groupManager->updateGroup($group, $oldPlatformRoleTransactionKey);

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return array(
            'group' => $group,
            'form_settings' => $form->createView()
        );
    }

 /**
     * @EXT\Route("/{groupId}/import", name="claro_admin_import_users_into_group_form")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template
     *
     * @param Group $group
     *
     * @return Response
     */
    public function importMembersFormAction(Group $group)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_USER_IMPORT);
        
        $files = array();
        $fs = new Filesystem();
        $finder = new Finder();
        $importFailureTempFolder = sys_get_temp_dir()."/claroline/import/failure/groups/".$group->getId();
        if ($fs->exists($importFailureTempFolder)) {
        	$finder->files()->in($importFailureTempFolder);
        	
	        foreach ($finder as $file) {
	        	$fileData = array();
	        	$fileData['path'] = $file->getRealPath(); 
	        	$filename = $file->getRelativePathname();
	        	$fileData['fileName'] = $filename;
	        	$explodedFilename = explode('_', explode('.', $filename)[0]); 
	        	$time = $explodedFilename[2]."_".$explodedFilename[3];
	        	$fileData['date'] = \DateTime::createFromFormat("Y-m-d_H-i", $time);
	        	$files[$time] = $fileData;
	        }
        
        	ksort($files);
        }

        return array('form' => $form->createView(), 'group' => $group, 'files' => $files);
    }

    /**
     * @EXT\Route("/import/download/reject", name="claro_admin_import_file_download")
     * @EXT\Method("GET")
     *
     * @return Response
     */
    public function downloadImportRejectFileAction()
    {
    	$this->checkOpen();
    	 
    	$filepath = $this->getRequest()->get("filepath");
    	
    	$content = file_get_contents($filepath);
    	 
    	return new Response($content, 200, array(
    			'Content-Type' => 'application/force-download',
    			'Content-Disposition' => 'attachment; filename="rejected_import_users.csv"'
    	));
    }
    

    /**
     * @EXT\Route("/import/delete/reject", name="claro_admin_import_file_delete")
     * @EXT\Method("GET")
     *
     * @return Response
     */
    public function deleteImportRejectFileAction()
    {
    	$this->checkOpen();

    	$filepath = $this->getRequest()->get("filepath");
    	
    	if (unlink($filepath)) {
    		return new Response(200);
    	} else {
    		return new Response(500);
    	}
    }
    
    

    /**
     * @EXT\Route("/{groupId}/import", name="claro_admin_import_users_into_group")
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration/Groups:importMembersForm.html.twig")
     *
     * @param Group $group
     *
     * @return Response
     */
    public function importMembersAction(Group $group)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_USER_IMPORT);
        $form->handleRequest($this->request);
        
        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $parsedFile = $this->filterImportUsers($file);
            
            if (count($parsedFile['valid']) > 0) {
	            $ctx    = new \ZMQContext();
	            $sender = new \ZMQSocket($ctx, \ZMQ::SOCKET_PUSH);
	            $sender->connect("tcp://localhost:11112");
	            
	            
	            $fileChunks = array_chunk($parsedFile['valid'], 500, true);
	            
	            foreach ($fileChunks as $fileChunk) {
		            $message = array(
		            		'class_name'	=> "ClarolineCoreBundle:User",
		            		'group'			=> $group->getId(),
		            		'users' 		=> $fileChunk,
		            		'user'			=> $this->getUser()->getId()
		            );
		            $sender->send(json_encode($message));
	            }
            }
            
            $shouldWriteRejectFile = array();
            foreach ($parsedFile['rejected'] as $error => $lines) {
            	foreach ($lines as $lineIndex => $line) {
            		$shouldWriteRejectFile = true;
            		break 2;
            	}
            }
            
			if ($shouldWriteRejectFile) {
	            $handle = fopen('php://memory', 'r+');

	            foreach ($parsedFile['rejected'] as $error => $lines) {
	            	switch($error) {
	            		case "file_mail":
	            			$errorString = "Duplicate mail addresses in file";
	            			break;

            			case "file_username":
            				$errorString = "Duplicate usernames in file";
            				break;

            			case "db_mail":
            				$errorString = "Mail address already exists in database";
            				break;

            			case "db_username":
            				$errorString = "Username already exists in database";
            				break;
            				
            			default:
            				$errorString = "Unknown error";
            				break;
	            	}
	            	fputs($handle, "*****    ".$errorString."    *****".PHP_EOL);
	            	foreach ($lines as $lineIndex => $line) {
	            		fputs($handle, $line.PHP_EOL);
	            	}
	            }
	            
	            rewind($handle);
	            $content = stream_get_contents($handle);
	            fclose($handle);
	            
	            $fs = new Filesystem();
	            $importFailureTempFolder = sys_get_temp_dir()."/claroline/import/failure/groups/".$group->getId();
	            if (!$fs->exists($importFailureTempFolder)) {
	            	$fs->mkdir($importFailureTempFolder, 0700);
	            }
            	$now = new \DateTime();
            	$filename = $importFailureTempFolder."/import_reject_".$now->format("Y-m-d_H-i").".csv";
            	$fs->touch($filename);
            	file_put_contents($filename, $content);
			}
            
            return new RedirectResponse(
                $this->router->generate('claro_admin_import_users_into_group_form', array('groupId' => $group->getId()))
            );
        } else {
	        return array('form' => $form->createView(), 'group' => $group);
        }
    }
    
    public function filterImportUsers($file) {
    	$result = array();
    	$result['rejected'] = array();
    	$result['rejected']['file_mail'] = array();
    	$result['rejected']['file_username'] = array();
    	$result['rejected']['db_mail'] = array();
    	$result['rejected']['db_username'] = array();
    	$result['valid'] = array();

    	$lines = str_getcsv(file_get_contents($file), PHP_EOL);
    	$users = array();
    	$usernames = array();
    	$mails = array();
    	
    	foreach ($lines as $i => $line) {
    		$result['valid'][$i] = str_getcsv($line, ";");
    		$user = explode(';', $line);
    		$firstName = $user[0];
    		$lastName = $user[1];
    		$username = $user[2];
    		$pwd = $user[3];
    		$email = $user[4];
    		$code = isset($user[5])? $user[5] : null;
    		$phone = isset($user[6])? $user[6] : null;
    	
    		if (!array_key_exists(strtolower($email), $mails)) {
	    		$mails[strtolower($email)] = array($i);
    		} else {
	    		$mails[strtolower($email)][] = $i;
    		}
    		
    		if (!array_key_exists(strtolower($username), $usernames)) {
	    		$usernames[strtolower($username)] = array($i);
    		} else {
	    		$usernames[strtolower($username)][] = $i;
    		}
    	
    		$newUser = new User();
    		$newUser->setFirstName($firstName);
    		$newUser->setLastName($lastName);
    		$newUser->setUsername($username);
    		$newUser->setPlainPassword($pwd);
    		$newUser->setMail($email);
    		$newUser->setAdministrativeCode($code);
    		$newUser->setPhone($phone);
    		$users[$i] = $newUser;
    	}
    	
    	foreach ($usernames as $username => $linesIndexes) {
    		$rejects = &$result['rejected']['file_username'];
    		if (count($linesIndexes) > 1) {
    			foreach ($linesIndexes as $lineIndex) {
    				$rejects[$lineIndex] = $lines[$lineIndex];
    				unset($result['valid'][$lineIndex]);
    			}
    			
    			/*$msg = $this->translator->trans(
    					'username_found_at',
    					array('%username%' => $username, '%lines%' => $this->getLines($lines)),
    					'platform'
    			) . ' ';*/
    		}
    		ksort($rejects);
    	}
    	
    	foreach ($mails as $mail => $linesIndexes) {
    		$rejects = &$result['rejected']['file_mail'];
    		if (count($linesIndexes) > 1) {
    			foreach ($linesIndexes as $lineIndex) {
    				$rejects[$lineIndex] = $lines[$lineIndex];
    				unset($result['valid'][$lineIndex]);
    			}
    			/*$msg = $this->translator->trans(
    					'email_found_at',
    					array('%email%' => $mail, '%lines%' => $this->getLines($lines)),
    					'platform'
    			) . ' ';*/
    		}
    		ksort($rejects);
    	}
    	
    	// Validate users
    	$repo = $this->getDoctrine()->getManager()->getRepository("ClarolineCoreBundle:User");
    	
    	$alreadyExistingUsernames = $repo->findByUsernameIn(array_keys($usernames));
    	$alreadyExistingMails = $repo->findByMailIn(array_keys($mails));
    	
    	$rejects = &$result['rejected']['db_username'];
    	foreach ($alreadyExistingUsernames as $alreadyExistingUser) {
    		foreach ($users as $lineIndex => $user) {
    			if (strtolower($user->getUsername()) == strtolower($alreadyExistingUser->getUsername())) {
    				$rejects[$lineIndex] = $lines[$lineIndex];    
    				unset($result['valid'][$lineIndex]);
    			}
    		}
    		/*$msg = $this->translator->trans(
    				'username_already_exists',
    				array('%username%' => $user->getUsername(), '%lines%' => $this->getLines($usernames[$user->getUsername()])),
    				'platform'
    		) . ' ';*/
    	}
    	ksort($rejects);

    	$rejects = &$result['rejected']['db_mail'];
    	foreach ($alreadyExistingMails as $alreadyExistingUser) {
    		foreach ($users as $lineIndex => $user) {
    			if (strtolower($user->getMail()) == strtolower($alreadyExistingUser->getMail())) {
    				$rejects[$lineIndex] = $lines[$lineIndex];
    				unset($result['valid'][$lineIndex]);
    			}
    		}
    		/*$msg = $this->translator->trans(
    				'mail_already_exists',
    				array('%email%' => $user->getMail(), '%lines%' => $this->getLines($usernames[$user->getUsername()])),
    				'platform'
    		) . ' ';*/
    	}
    	ksort($rejects);
    	return $result;
    }
    
    /**
     * @param \Symfony\Component\Form\Form $form
     *
     * @return array
     */
    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
    	$errors = array();
    
    	if ($form->count() > 0) {
    		foreach ($form->all() as $child) {
    			/**
    			 * @var \Symfony\Component\Form\Form $child
    			 */
    			if (!$child->isValid()) {
    				$errors[$child->getName()] = $this->getErrorMessages($child);
    			}
    		}
    	} else {
    		/**
    		 * @var \Symfony\Component\Form\FormError $error
    		 */
    		foreach ($form->getErrors() as $key => $error) {
    			$errors[] = $error->getMessage();
    		}
    	}
    
    	return $errors;
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->userAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
