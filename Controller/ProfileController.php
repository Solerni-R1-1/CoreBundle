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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\UserPublicProfilePreferences;
use Claroline\CoreBundle\Form\UserPublicProfilePreferencesType;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\ResetPasswordType;
use Claroline\CoreBundle\Form\UserPublicProfileUrlType;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use Symfony\Component\Form\FormError;

/**
 * Controller of the user profile.
 */
class ProfileController extends Controller
{
    private $userManager;
    private $roleManager;
    private $eventDispatcher;
    private $security;
    private $request;
    private $localeManager;
    private $encoderFactory;

    /**
     * @DI\InjectParams({
     *     "userManager"     = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"        = @DI\Inject("security.context"),
     *     "request"         = @DI\Inject("request"),
     *     "localeManager"   = @DI\Inject("claroline.common.locale_manager"),
     *     "encoderFactory"  = @DI\Inject("security.encoder_factory")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        StrictDispatcher $eventDispatcher,
        SecurityContextInterface $security,
        Request $request,
        LocaleManager $localeManager,
        EncoderFactoryInterface $encoderFactory
    )
    {
        $this->userManager      = $userManager;
        $this->roleManager      = $roleManager;
        $this->eventDispatcher  = $eventDispatcher;
        $this->security         = $security;
        $this->request          = $request;
        $this->localeManager    = $localeManager;
        $this->encoderFactory   = $encoderFactory;
    }

    private function isInRoles($role, $roles)
    {
        foreach ($roles as $current) {
            if ($role->getId() == $current->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @EXT\Route(
     *     "/",
     *      name="claro_profile_view"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function viewAction(User $loggedUser, $page = 1)
    {
//         $doctrine = $this->getDoctrine();
//         $doctrine->getManager()->getFilters()->disable('softdeleteable');

//         $query   = $doctrine->getRepository('ClarolineCoreBundle:Badge\UserBadge')->findByUser($loggedUser, false);
//         $adapter = new DoctrineORMAdapter($query);
//         $pager   = new Pagerfanta($adapter);

//         try {
//             $pager->setCurrentPage($page);
//         } catch (NotValidCurrentPageException $exception) {
//             throw new NotFoundHttpException();
//         }
    	// If user share base information, fetch the data about forum messages, badges and followed moocs
    	$nbBadges = $loggedUser->getBadges()->count();
    	$messageRepository = $this->getDoctrine()->getRepository('ClarolineForumBundle:Message');
    	$nbPostedMessages = $messageRepository->countUserMessages($loggedUser);
    	$nbVotedMessages = $messageRepository->countUserMessagesLiked($loggedUser);

        return array(
            'user'  => $loggedUser,
// 			'pager'    => $pager,
        	'nbBadges' => $nbBadges,
        	'nbPostedMessages' => $nbPostedMessages,
        	'nbVotedMessages' => $nbVotedMessages
        );
    }

    /**
     * @EXT\Route(
     *     "/preferences",
     *      name="claro_user_public_profile_preferences"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editPublicProfilePreferencesAction(User $loggedUser)
    {
        $form    = $this->createForm(new UserPublicProfilePreferencesType(), $loggedUser->getPublicProfilePreferences());
        $userMoocPreferencesArray = array();

        // create also forms for each userMoocPreferences (right now, the only pref. is visibility)
        $formMooc = array();
        $userMoocPreferencesRepository = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Mooc\UserMoocPreferences');
        // create new forms for each userMoocPreferences entity
        foreach ($loggedUser->getMoocSessions() as $moocSession ) {
            $mooc = $moocSession->getMooc();
            $userMoocPreferenceEntity = $userMoocPreferencesRepository->findOneBy( array('mooc' => $mooc, 'user' => $loggedUser ) );
            // New empty entity
            if ( ! $userMoocPreferenceEntity ) {
                $userMoocPreferenceEntity = new \Claroline\CoreBundle\Entity\Mooc\UserMoocPreferences();
                $userMoocPreferenceEntity->setMooc($mooc)->setUser($loggedUser);
            }
            // We need a name identifier different for each formType
            $formType = new \Claroline\CoreBundle\Form\Mooc\userMoocPreferencesType( $mooc->getId() );

            // Create array of form with mooc object and create form object
            $userMoocPreferencesArray[] = array( 'mooc' => $mooc, 'userMoocPreferencesForm' => $this->createForm( $formType, $userMoocPreferenceEntity ));
        }

        // Form submit
        if ($this->request->isMethod('POST')) {
            // prepare entity manager
            $entityManager = $this->get('doctrine.orm.entity_manager');
            // handle requests
            $form->handleRequest($this->request);

            // For each userMoocPreferences persist entity
            foreach ( $userMoocPreferencesArray as $userMoocPreferences ) {
                $userMoocPreferencesForm = $userMoocPreferences['userMoocPreferencesForm'];
                $userMoocPreferencesForm->handleRequest($this->request);
                if ( $userMoocPreferencesForm->isValid() ) {
                    $userMoocPreferenceEntity = $userMoocPreferencesForm->getData();
                    $entityManager->persist($userMoocPreferenceEntity);
                }
            }

            if ($form->isValid()) {
                /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
                $sessionFlashBag = $this->get('session')->getFlashBag();
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');

                try {
                    /** @var \Claroline\CoreBundle\Entity\UserPublicProfilePreferences $userPublicProfilePreferences */
                    $userPublicProfilePreferences = $form->getData();

                    if ($userPublicProfilePreferences !== $loggedUser->getPublicProfilePreferences()) {
                        throw new \Exception();
                    }

                    $entityManager->persist($userPublicProfilePreferences);
                    $entityManager->flush();

                    $sessionFlashBag->add('success', $translator->trans('edit_public_profile_preferences_success', array(), 'platform'));
                } catch(\Exception $exception){
                    $sessionFlashBag->add('error', $translator->trans('edit_public_profile_preferences_error', array(), 'platform'));
                }

                return $this->redirect($this->generateUrl('claro_profile_view'));
            }
        }

        $nbBadges = $loggedUser->getBadges()->count();
        $messageRepository = $this->getDoctrine()->getRepository('ClarolineForumBundle:Message');
        $nbPostedMessages = $messageRepository->countUserMessages($loggedUser);
    	$nbVotedMessages = $messageRepository->countUserMessagesLiked($loggedUser);

        return array(
            'form' => $form->createView(),
            'userMoocPreferencesArray' => $userMoocPreferencesArray,
            'user' => $loggedUser,
        	'nbBadges' => $nbBadges,
        	'nbPostedMessages' => $nbPostedMessages,
        	'nbVotedMessages' => $nbVotedMessages
        );
    }

    /**
     * @EXT\Route(
     *     "/{publicUrl}",
     *      name="claro_public_profile_view",
     *      options={"expose"=true}
     * )
     */
    public function publicProfileAction($publicUrl)
    {
        /* @var $user User */
        $user = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByIdOrPublicUrl($publicUrl);

        if (null === $user) {
            return new Response( $this->renderView('ClarolineCoreBundle:Profile:publicProfile.204.html.twig', array(), 204 ) );
        }

        $userPublicProfilePreferences = $user->getPublicProfilePreferences();
        $publicProfileVisible         = false;

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $userPublicProfilePreferences = $this->get('claroline.manager.user_manager')->getUserPublicProfilePreferencesForAdmin();
        }

        if(UserPublicProfilePreferences::SHARE_POLICY_NOBODY === $userPublicProfilePreferences->getSharePolicy()) {
            $response = new Response($this->renderView('ClarolineCoreBundle:Profile:publicProfile.404.html.twig', array('user' => $user, 'publicUrl' => $publicUrl)), 404);
        }
        else if (UserPublicProfilePreferences::SHARE_POLICY_PLATFORM_USER === $userPublicProfilePreferences->getSharePolicy()
                 && null === $this->getUser()) {
            $response = new Response($this->renderView('ClarolineCoreBundle:Profile:publicProfile.401.html.twig', array('user' => $user, 'publicUrl' => $publicUrl)), 401);
        } else {
        	// If user share base information, fetch the data about forum messages, badges and followed moocs
        	$nbBadges = $user->getBadges()->count();
        	$messageRepository = $this->getDoctrine()->getRepository('ClarolineForumBundle:Message');
	    	$nbPostedMessages = $messageRepository->countUserMessages($user);
	    	$nbVotedMessages = $messageRepository->countUserMessagesLiked($user);
        	$response = new Response($this->renderView('ClarolineCoreBundle:Profile:publicProfile.html.twig', array(
        			'user' => $user,
        			'publicProfilePreferences' => $userPublicProfilePreferences,
        			'nbBadges' => $nbBadges,
        			'nbPostedMessages' => $nbPostedMessages,
        			'nbVotedMessages' => $nbVotedMessages
        	)));
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/profile/edit/{user}",
     *     name="claro_user_profile_edit"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     *
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editProfileAction(User $loggedUser, User $user = null)
    {
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        $editYourself = false;

        if (null !== $user && !$isAdmin) {
            throw new AccessDeniedException();
        }

        if (null === $user) {
            $user         = $loggedUser;
            $editYourself = true;
        }

        $roles = $this->roleManager->getPlatformRoles($user);
        $form = $this->createForm(
            new ProfileType($roles, $isAdmin, $this->localeManager->getAvailableLocales()), $user
        );

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');

            $user = $form->getData();

            // Public URL cannot be empty
            if ( ! $user->getPublicUrl() ) {
                $form->get('publicUrl')->addError(new FormError($translator->trans('public_url_help', array(), 'platform')));
                 return array(
                    'form'         => $form->createView(),
                    'user'         => $user,
                    'editYourself' => $editYourself
                );
            }

            // Check if publicUrl is already used by someone else
            $searchedUsers = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl($user->getPublicUrl());
            if ( $searchedUsers && $searchedUsers->getId() != $user->getId() ) {
                $form->get('publicUrl')->addError(new FormError($translator->trans('public_url_double', array(), 'platform')));
                return array(
                    'form'         => $form->createView(),
                    'user'         => $user,
                    'editYourself' => $editYourself
                );
            }


            $this->userManager->rename($user, $user->getUsername());

            $successMessage = $translator->trans('edit_profile_success', array(), 'platform');
            $errorMessage   = $translator->trans('edit_profile_error', array(), 'platform');
            $redirectUrl    = $this->generateUrl('claro_admin_user_list');
            if ($editYourself) {
                $successMessage = $translator->trans('edit_your_profile_success', array(), 'platform');
                $errorMessage   = $translator->trans('edit_your_profile_error', array(), 'platform');
                $redirectUrl    = $this->generateUrl('claro_profile_view');
            }

            try {
                $entityManager = $this->getDoctrine()->getManager();
                $unitOfWork    = $entityManager->getUnitOfWork();
                $unitOfWork->computeChangeSets();

                $changeSet = $unitOfWork->getEntityChangeSet($user);
                $newRoles  = array();

                if (isset($form['platformRoles'])) {
                    $newRoles = $form['platformRoles']->getData();
                    $this->userManager->setPlatformRoles($user, $newRoles);
                }

                $rolesChangeSet = array();
                //Detect added
                foreach ($newRoles as $role) {
                    if (!$this->isInRoles($role, $roles)) {
                        $rolesChangeSet[$role->getTranslationKey()] = array(false, true);
                    }
                }
                //Detect removed
                foreach ($roles as $role) {
                    if (!$this->isInRoles($role, $newRoles)) {
                        $rolesChangeSet[$role->getTranslationKey()] = array(true, false);
                    }
                }
                if (count($rolesChangeSet) > 0) {
                    $changeSet['roles'] = $rolesChangeSet;
                }

                $this->userManager->uploadAvatar($user);
                $this->eventDispatcher->dispatch(
                    'log',
                    'Log\LogUserUpdate',
                    array($user, $changeSet)
                );

                $sessionFlashBag->add('success', $successMessage);
            } catch(\Exception $exception){
                $sessionFlashBag->add('error', $errorMessage);
            }

            return $this->redirect($redirectUrl);
        }

        return array(
            'form'         => $form->createView(),
            'user'         => $user,
            'editYourself' => $editYourself
        );
    }

    /**
     * @EXT\Route(
     *     "/password/edit",
     *      name="claro_user_password_edit"
     * )
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function editPasswordAction(User $loggedUser)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');

        $form = $this->createForm(new ResetPasswordType($translator, true));
        $oldPassword = $loggedUser->getPassword();
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');
            $loggedUser->setPlainPassword($form['password']->getData());

            if ($this->encodePassword($loggedUser) === $oldPassword) {
                $loggedUser->setPlainPassword($form['plainPassword']->getData());
                $loggedUser->setPassword($this->encodePassword($loggedUser));
                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($loggedUser);
                $entityManager->flush();

                $this->userManager->sendEmailChangePassword($loggedUser);

                $sessionFlashBag->add('success', $translator->trans('edit_password_success', array(), 'platform'));
                return $this->redirect($this->generateUrl('claro_profile_view'));
            } else {
                $sessionFlashBag->add('error', $translator->trans('edit_password_error_current', array(), 'platform'));
                return $this->redirect($this->generateUrl('claro_user_password_edit'));
            }

        }

        return array(
            'form' => $form->createView(),
            'user' => $loggedUser
        );
    }

    /**
     * @EXT\Route(
     *     "/publicurl/edit",
     *      name="claro_user_public_url_edit"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editPublicUrlAction(User $loggedUser)
    {

        // Redirect user if he has already modified his public URL
        if ( $loggedUser->hasTunedPublicUrl() ) {
            return $this->redirect($this->generateUrl('claro_profile_view'));
        }

        $currentPublicUrl = $loggedUser->getPublicUrl();
        $form = $this->createForm(new UserPublicProfileUrlType(), $loggedUser);
        $form->handleRequest($this->request);

        if ($form->isValid()) {

            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');

            /** @var \Claroline\CoreBundle\Entity\User $user */
            $user = $form->getData();

            // Public URL cannot be empty
            if ( ! $user->getPublicUrl() ) {
                $form->get('publicUrl')->addError(new FormError($translator->trans('public_url_help', array(), 'platform')));
                 return array(
                    'form'             => $form->createView(),
                    'user'             => $loggedUser,
                    'currentPublicUrl' => $currentPublicUrl
                );
            }

            // Check if publicUrl is already used
            $searchedUsers = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl($user->getPublicUrl());
            if ( $searchedUsers ) {
                $form->get('publicUrl')->addError(new FormError($translator->trans('public_url_double', array(), 'platform')));
                return array(
                    'form'             => $form->createView(),
                    'user'             => $loggedUser,
                    'currentPublicUrl' => $currentPublicUrl
                );
            }

            try {
                $user->setHasTunedPublicUrl(true);

                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($user);
                $entityManager->flush();

                $sessionFlashBag->add('success', $translator->trans('tune_public_url_success', array(), 'platform'));
            } catch(\Exception $exception){
                $sessionFlashBag->add('error', $translator->trans('tune_public_url_error', array(), 'platform'));
            }

            return $this->redirect($this->generateUrl('claro_profile_view'));
        }

        return array(
            'form'             => $form->createView(),
            'user'             => $loggedUser,
            'currentPublicUrl' => $currentPublicUrl
        );
    }

    /**
     * @EXT\Route(
     *     "/publicurl/check",
     *      name="claro_user_public_url_check"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Method({"POST"})
     */
    public function checkPublicUrlAction(Request $request)
    {
        $publicUrl = $request->request->get('publicUrl');
        $isValid = true;

        // If it's always okay :
        //  We test the pattern
        if($isValid){
            $pattern = User::$patternUrlPublic;
            if(!preg_match($pattern, $publicUrl)) {
                $isValid = false;
            }
        }

        // If it's always okay :
        //  We test the unicity
        if($isValid){
            $existedUser = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl($publicUrl);
            if (null !== $existedUser) {
                $isValid = false;
            }
        }

        $data = array('check' => $isValid);

        $response = new JsonResponse($data);
        return $response;
    }

    /**
     * @EXT\Route(
     *     "/delete/{userId}",
     *      name="claro_user_delete_page"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @EXT\Method({"GET"})
     */
    public function deleteUserProfileAction( User $loggedUser )
    {

        return array(
            'user' => $loggedUser
        );

    }

    /**
     * @EXT\Route(
     *     "/delete/{userId}",
     *      name="claro_user_delete_action"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @EXT\Method({"POST"})
     */
    public function deleteUserProfilePostAction( User $loggedUser )
    {
        $this->userManager->deleteUser($loggedUser);
        $this->eventDispatcher->dispatch('log', 'Log\LogUserDelete', array($loggedUser));
        $this->security->setToken(NULL);

        return $this->redirect( $this->generateUrl('solerni_static_page', array( 'name' => 'cms_url' ) ) );
    }

    private function encodePassword(User $user)
    {
        return $this->encoderFactory
            ->getEncoder($user)
            ->encodePassword($user->getPlainPassword(), $user->getSalt());
    }
}
