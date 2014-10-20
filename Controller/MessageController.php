<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\MessageManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Symfony\Component\Translation\TranslatorInterFace;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 */
class MessageController
{
    private $request;
    private $router;
    private $formFactory;
    private $messageManager;
    private $groupManager;
    private $userManager;
    private $workspaceManager;
    private $securityContext;
    private $utils;
    private $pagerFactory;
    private $mailManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "manager"            = @DI\Inject("claroline.manager.message_manager"),
     *     "groupManager"       = @DI\Inject("claroline.manager.group_manager"),
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "utils"              = @DI\Inject("claroline.security.utilities"),
     *     "pagerFactory"       = @DI\Inject("claroline.pager.pager_factory"),
     *     "mailManager"        = @DI\Inject("claroline.manager.mail_manager"),
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct(
        Request $request,
        UrlGeneratorInterface $router,
        FormFactory $formFactory,
        MessageManager $manager,
        GroupManager $groupManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        SecurityContextInterface $securityContext,
        Utilities $utils,
        PagerFactory $pagerFactory,
        MailManager $mailManager,
        TranslatorInterface $translator
    )
    {
        $this->request = $request;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->messageManager = $manager;
        $this->groupManager = $groupManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->securityContext = $securityContext;
        $this->utils = $utils;
        $this->pagerFactory = $pagerFactory;
        $this->mailManager = $mailManager;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "/form/group/{group}",
     *     name="claro_message_form_for_group"
     * )
     *
     * Displays the message form with the "to" field filled with users of a group.
     *
     * @param Group $group
     *
     * @return Response
     */
    public function formForGroupAction(Group $group)
    {
        $url = $this->router->generate('claro_message_show', array('message' => 0))
            . '?grpsIds[]=' . $group->getId();

        return new RedirectResponse($url);
    }

    /**
     * @EXT\Route(
     *     "/form/workspace/{workspace}",
     *     name="claro_message_form_for_workspace"
     * )
     *
     * Displays the message form with the "to" field filled with users of a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function formForWorkspaceAction(AbstractWorkspace $workspace)
    {
        $url = $this->router->generate('claro_message_show', array('message' => 0))
            . '?wsIds[]=' . $workspace->getId();

        return new RedirectResponse($url);
    }

    /**
     * @EXT\Route(
     *     "/send/{rootId}",
     *     name="claro_message_send",
     *     defaults={"rootId" = 0}
     * )
     * @EXT\Method({"POST"})
     * @EXT\ParamConverter("sender", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "root",
     *     class="ClarolineCoreBundle:Message",
     *     options={"id" = "rootId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Message:show.html.twig")
     *
     * Handles the message form submission.
     *
     * @param User    $sender
     * @param Message $root
     *
     * @return Response
     */
    public function sendAction(User $sender, Message $root = null)
    {
        //First message
        if($root == null){
            $form = $this->formFactory->create(FormFactory::TYPE_MESSAGE, array(null, null, $this->translator));
            $form->handleRequest($this->request);

            if ($form->isValid()) {
                $message = $form->getData();
                $message->setSender($sender);
                $message->setRoot($message); //pointer on itself
                $message = $this->messageManager->send($message);
                $url = $this->router->generate('claro_message_show', array('message' => $message->getId()));

                return new RedirectResponse($url);
            }

        //Reply
        } else {
            $form = $this->formFactory->create(FormFactory::TYPE_MESSAGE_SIMPLE, array($this->translator));
            $form->handleRequest($this->request);

            $receivers = $this->getReceivers($sender, $root);

            $receiversUsername = array();
            foreach ($receivers as $id => $user_tmp) {
                $receiversUsername[] = $user_tmp->getUsername();
            }

            if ($form->isValid()) {
                $message = new Message();
                $datas = $form->getData();
                $message->setContent($datas['content']);
                $message->setSender($sender);
                $message->setRoot($root->getRoot());
                $message->setObject($root->getObject());
                $message->setTo(implode(";", $receiversUsername));
                $message = $this->messageManager->send($message);
                $url = $this->router->generate('claro_message_show', array('message' => $message->getId()));

                return new RedirectResponse($url);
            } 
        }
        

        

        $disqus = $root ? $this->messageManager->getConversation($root): array();

        return array('form' => $form->createView(), 'message' => $root, 'disqus' => $disqus);
    }

    /**
     * @EXT\Route(
     *     "/received/page/{page}",
     *     name="claro_message_list_received",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/received/page/{page}/search/{search}",
     *     name="claro_message_list_received_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("receiver", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the messages received by a user, optionally filtered by a search
     * on the object or the sender username.
     *
     * @param User    $receiver
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function listReceivedAction(User $receiver, $page, $search)
    {
        $pager = $this->messageManager->getReceivedMessages($receiver, $search, $page);

        return array(
            'pager' => $pager,
            'search' => $search,
            'isMailerAvailable' => $this->mailManager->isMailerAvailable()
        );
    }

    /**
     * @EXT\Route(
     *     "/sent/page/{page}",
     *     name="claro_message_list_sent",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/sent/page/{page}/search/{search}",
     *     name="claro_message_list_sent_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("sender", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the messages sent by a user, optionally filtered by a search
     * on the object.
     *
     * @param User    $sender
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function listSentAction(User $sender, $page, $search)
    {
        $pager = $this->messageManager->getSentMessages($sender, $search, $page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/removed/page/{page}",
     *     name="claro_message_list_removed",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/removed/page/{page}/search/{search}",
     *     name="claro_message_list_removed_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     *
     * Displays the messages removed by a user, optionally filtered by a search
     * on the object or the sender username.
     *
     * @param User    $user
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function listRemovedAction(User $user, $page, $search)
    {
        $pager = $this->messageManager->getRemovedMessages($user, $search, $page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/show/{message}",
     *     name="claro_message_show",
     *     defaults={"message"=0}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "receivers",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true, "name"="wsIds"}
     * )
     * @EXT\ParamConverter(
     *      "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name"="grpsIds"}
     * )
     * @EXT\ParamConverter(
     *      "message",
     *      class="ClarolineCoreBundle:Message",
     *      options={"id" = "message", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Displays a message.
     *
     * @param User    $user
     * @param array   $receivers
     * @param array   $groups
     * @param array   $workspaces
     * @param Message $message
     * @param string    $form
     *
     * @return Response
     */
    public function showAction(
        User $user,
        array $receivers,
        array $groups,
        array $workspaces,
        Message $message = null
    )
    {
        if ($message) {
            //Get all conversation, even message with ID > current id asked
            $disqus = $this->messageManager->getConversation($message);

            // Mark all conversation as readed
            $this->messageManager->markAsRead($user, $disqus);

            
           /* foreach ($disqus as $key => $msg) {
                echo $msg->getId();
                echo "<hr/>";
            }*/
            //$receivers = $this->getReceivers($user, $message);

            /*foreach ($receivers as $id => $user_tmp) {
                echo $user_tmp->getUsername().' ';
            }
            echo '<hr/>';*/

            //$sendString = $this->messageManager->generateStringTo(array_values($receivers), $groups, $workspaces);
        //die($sendString);
            

            //$object = 'Re: ' . $message->getObject();
            $this->checkAccess($message, $user);
            $form = $this->formFactory->create(FormFactory::TYPE_MESSAGE_SIMPLE, array($this->translator)); 
        } else {
            //datas from the post request
            //$sendString = $this->messageManager->generateStringTo($receivers, $groups, $workspaces);
            //$object = '';
            $disqus = array();
            $form = $this->formFactory->create(FormFactory::TYPE_MESSAGE, array(null, null, $this->translator));
        }
        

        return array(
            'disqus' => $disqus,
            'message' => $message,
            'form' => $form->createView()
        );
    }

    private function getReceivers($user, $previousMessage){
        $receivers = array();

        // We add the sender
        // except if the sender is ourself
        if( $previousMessage->getSenderUsername() !== $user->getUserName()) {
            $userSender = $this->userManager->getUserByUsername($previousMessage->getSenderUsername()); 
            if($userSender != null){
                $receivers[$userSender->getId()] = $userSender; 
            }
        }

        $dests = $previousMessage->getUserMessages();
        foreach ($dests as $dest) {

            $user_receivers = $dest->getUser();

            // We add the receiver
            // except if it's ourself
            // except if the receiver is also the sender to avoid spam
            if($user_receivers->getId() !== $user->getId()
                && !array_key_exists($user_receivers->getId(), $receivers)) {
                $receivers[$user_receivers->getId()] = $user_receivers;   
            } 
        }

        if(count($receivers) == 0){
            $userSender = $this->userManager->getUserByUsername($previousMessage->getSenderUsername()); 
            if($userSender != null){
                $receivers[$userSender->getId()] = $userSender; 
            }
        }

        return $receivers;
    }
    

    /**
     * @EXT\Route(
     *     "/remove",
     *     name="claro_message_soft_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:UserMessage", options={"multipleIds" = true})
     *
     * Moves messages from the list of sent or received messages to the trash bin.
     *
     * @param User           $user
     * @param array[Message] $messages
     *
     * @return Response
     */
    public function softDeleteAction(User $user, array $messages)
    {
        $this->messageManager->markAsRemoved($messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/delete",
     *     name="claro_message_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:UserMessage", options={"multipleIds" = true})
     *
     * Deletes permanently a set of messages received or sent by a user.
     *
     * @param User           $user
     * @param array[Message] $messages
     *
     * @return Response
     */
    public function deleteAction(User $user, array $messages)
    {
        $this->messageManager->remove($messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/restore",
     *     name="claro_message_restore_from_trash",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:UserMessage", options={"multipleIds" = true})
     *
     * Restores messages previously moved to the trash bin.
     *
     * @param User           $user
     * @param array[Message] $messages
     *
     * @return Response
     */
    public function restoreFromTrashAction(User $user, array $messages)
    {
        $this->messageManager->markAsUnremoved($messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/mark_as_read/{message}",
     *     name="claro_message_mark_as_read",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Marks a message as read.
     *
     * @param User    $user
     * @param Message $message
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markAsReadAction(User $user, Message $message)
    {
        $this->messageManager->markAsRead($user, array($message));

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/contactable/users/page/{page}",
     *     name="claro_message_contactable_users",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/contactable/users/page/{page}/search/{search}",
     *     name="claro_message_contactable_users_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     *
     * Displays the list of users that the current user can send a message to,
     * optionally filtered by a search on first name and last name
     *
     * @param integer $page
     * @param string  $search
     * @param User    $user
     *
     * @return Response
     */
    public function contactableUsersListAction(User $user, $page, $search)
    {
        $trimmedSearch = trim($search);

        if ($user->hasRole('ROLE_ADMIN')) {
            if ($trimmedSearch === '') {
                $users = $this->userManager->getAllUsers($page);
            } else {
                $users = $this->userManager
                    ->getAllUsersBySearch($page, $trimmedSearch);
            }
        } else {
            $users = array();
            $token = $this->securityContext->getToken();
            $roles = $this->utils->getRoles($token);
            $workspaces = $this->workspaceManager->getOpenableWorkspacesByRoles($roles);

            if (count($workspaces) > 0) {
                if ($trimmedSearch === '') {
                    $users = $this->userManager
                        ->getUsersByWorkspaces($workspaces, $page);
                } else {
                    $users = $this->userManager->getUsersByWorkspacesAndSearch(
                        $workspaces,
                        $page,
                        $search
                    );
                }
            }
        }

        return array('users' => $users, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/notification/{isNotified}",
     *     name="claro_message_notification",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param boolean $isNotified
     * @param User    $user
     *
     * @return Response
     */
    public function setMailNotificationAction($isNotified, User $user)
    {
        $this->userManager->setIsMailNotified($user, $isNotified);

        return new JsonResponse(array('success' => 'success'));
    }

    /**
     * @EXT\Route(
     *     "/contactable/groups/page/{page}",
     *     name="claro_message_contactable_groups",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/contactable/groups/page/{page}/search/{search}",
     *     name="claro_message_contactable_groups_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     *
     * Displays the list of groups that the current user can send a message to,
     * optionally filtered by a search on group name
     *
     * @param integer $page
     * @param string  $search
     * @param User    $user
     *
     * @return Response
     */
    public function contactableGroupsListAction(User $user, $page, $search)
    {
        $trimmedSearch = trim($search);

        if ($user->hasRole('ROLE_ADMIN')) {
            if ($trimmedSearch === '') {
                $groups = $this->groupManager->getAllGroups($page);
            } else {
                $groups = $this->groupManager
                    ->getAllGroupsBySearch($page, $trimmedSearch);
            }
        } else {
            $groups = array();
            $workspaces = $this->workspaceManager
                ->getWorkspacesByUserAndRoleNames($user, array('ROLE_WS_MANAGER'));
            // retrieve all groups of workspace that user is manager
            if (count($workspaces) > 0) {
                if ($trimmedSearch === '') {
                    $groups = $this->groupManager
                        ->getGroupsByWorkspaces($workspaces);
                } else {
                    $groups = $this->groupManager->getGroupsByWorkspacesAndSearch(
                        $workspaces,
                        $search
                    );
                }
            }
            // get groups in which user is subscribed
            $userGroups = $user->getGroups();
            $userGroupsFinal = array();

            if ($trimmedSearch === '') {
                $userGroupsFinal = $userGroups;
            } else {
                $upperSearch = strtoupper($trimmedSearch);

                foreach ($userGroups as $userGroup) {
                    $upperName = strtoupper($userGroup->getName());

                    if (strpos($upperName, $upperSearch) !== false) {
                        $userGroupsFinal[] = $userGroup;
                    }
                }
            }
            // merge the 2 groups array
            foreach ($userGroupsFinal as $userGroupFinal) {
                if (!in_array($userGroupFinal, $groups)) {
                    $groups[] = $userGroupFinal;
                }
            }

            $this->pagerFactory->createPagerFromArray($groups, $page);
        }

        return array('groups' => $groups, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/contactable/workspaces/page/{page}",
     *     name="claro_message_contactable_workspaces",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/contactable/workspaces/page/{page}/search/{search}",
     *     name="claro_message_contactable_workspaces_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     *
     * Displays the list of groups that the current user can send a message to,
     * optionally filtered by a search on group name
     *
     * @param integer $page
     * @param string  $search
     * @param User    $user
     *
     * @return Response
     */
    public function contactableWorkspacesListAction(User $user, $page, $search)
    {
        $workspaces = $this->workspaceManager->getWorkspacesByManager($user);
        $workspaces = $this->pagerFactory->createPagerFromArray($workspaces, $page);

        return array('workspaces' => $workspaces, 'search' => $search);
    }

    public function checkAccess(Message $message, User $user)
    {
        if ($message->getSenderUsername() === $user->getUsername()) {
            return true;
        }

        $receiverString = $message->getTo();
        $names = explode(';', $receiverString);
        $usernames = array();
        $groupNames = array();

        foreach ($names as $name) {
            if (substr($name, 0, 1) === '{') {
                $groupNames[] = trim($name, '{}');
            } else {
                $usernames[] = $name;
            }
        }

        $groups = $this->groupManager->getGroupsByNames($groupNames);

        foreach ($groups as $group) {
            $users = $this->userManager->getUsersByGroupWithoutPager($group);

            foreach ($users as $user) {
                $usernames[] = $user->getUsername();
            }
        }

        foreach ($usernames as $username) {
            if (strtolower($user->getUsername()) === strtolower($username)) {
                return true;
            }
        }

        if($message->getRoot() != null){
            return $this->checkAccess($message->getRoot(), $user);
        }

        throw new AccessDeniedException("This isn't your message");
    }
}
