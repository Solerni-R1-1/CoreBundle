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

use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Translation\Translator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Library\HttpFoundation\XmlResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Manager\MailManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Authentication/login controller.
 */
class AuthenticationController
{
    private $request;
    private $userManager;
    private $encoderFactory;
    private $om;
    private $mailManager;
    private $translator;
    private $formFactory;
    private $authenticator;
    private $router;

    /**
     * @DI\InjectParams({
     *     "request"        = @DI\Inject("request"),
     *     "userManager"    = @DI\Inject("claroline.manager.user_manager"),
     *     "encoderFactory" = @DI\Inject("security.encoder_factory"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator"     = @DI\Inject("translator"),
     *     "formFactory"    = @DI\Inject("claroline.form.factory"),
     *     "authenticator"  = @DI\Inject("claroline.authenticator"),
     *     "mailManager"    = @DI\Inject("claroline.manager.mail_manager"),
     *     "router"         = @DI\Inject("router")
     * })
     */
    public function __construct(
        Request $request,
        UserManager $userManager,
        EncoderFactory $encoderFactory,
        ObjectManager $om,
        Translator $translator,
        FormFactory $formFactory,
        Authenticator $authenticator,
        MailManager $mailManager,
        RouterInterface $router
    )
    {
        $this->request = $request;
        $this->userManager = $userManager;
        $this->encoderFactory = $encoderFactory;
        $this->om = $om;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->authenticator = $authenticator;
        $this->mailManager = $mailManager;
        $this->router = $router;
    }

    /**
     * @Route(
     *     "/login",
     *     name="claro_security_login",
     *     options={"expose"=true}
     * )
     * @Template()
     *
     * Standard Symfony form login controller.
     *
     * @see http://symfony.com/doc/current/book/security.html#using-a-traditional-login-form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
    	$data = array();
    	$session = $this->request->getSession();


        if ($this->request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $this->request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        $lastUsername = $session->get(SecurityContext::LAST_USERNAME);

        if ($session->has("moocSession")) {
        	$moocSession = $session->get("moocSession");
        	$moocSession = $this->om->getRepository("ClarolineCoreBundle:Mooc\MoocSession")->find($moocSession->getId());
        	$data['moocSession'] = $moocSession;
        }

        if ($session->has("privateMoocSession")) {
        	$moocSession = $session->get("privateMoocSession");
        	$moocSession = $this->om->getRepository("ClarolineCoreBundle:Mooc\MoocSession")->find($moocSession->getId());
        	$data['privateMoocSession'] = $moocSession;
        }

        $data['last_username'] = $lastUsername;
        $data['error'] = $error;
        return $data;
    }

    /**
     * @Route(
     *     "/reset",
     *     name="claro_security_forgot_password",
     *     options={"expose"=true}
     * )
     * @Template("ClarolineCoreBundle:Authentication:forgotPassword.html.twig")
     */
    public function forgotPasswordAction()
    {
        if ($this->mailManager->isMailerAvailable()) {
            $form = $this->formFactory->create(FormFactory::TYPE_USER_EMAIL, array($this->translator));

            return array('form' => $form->createView());
        }

        return array(
            'error' =>
                $this->translator->trans('mail_not_available', array(), 'platform')
                . ' '
                . $this->translator->trans('mail_config_problem', array(), 'platform')
        );
    }

    /**
     * @Route(
     *     "/passwords/reset",
     *     name="claro_security_initialize_password",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "ids"}
     * )
     */
    public function passwordInitializationAction(array $users)
    {
        foreach ($users as $user) {
            $user->setHashTime(time());
            $password = sha1(rand(1000, 10000) . $user->getUsername() . $user->getSalt());
            $user->setResetPasswordHash($password);
            $this->om->persist($user);
            $this->om->flush();
            $this->mailManager->sendForgotPassword($user);
        }

        return new Response(204);
    }

    /**
     * @Route(
     *     "/sendmail",
     *     name="claro_security_send_token",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     * @Template("ClarolineCoreBundle:Authentication:forgotPassword.html.twig")
     */
    public function sendEmailAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_USER_EMAIL, array($this->translator), null);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();
            $user = $this->userManager->getUserbyEmail($data['mail']);

            if (!empty($user)) {

                if ( $user->isFacebookAccount() === TRUE ) {
                    return array(
                        'error' => $this->translator->trans('fb_forbidden', array(), 'platform'),
                        'form' => $form->createView()
                    );
                } else if ($user->isLockedPassword() === TRUE) {

                    return array(
                        'error' => $this->translator->trans('syfadis_forbidden', array(), 'platform'),
                        'form' => $form->createView()
                    );
                }

                $user->setHashTime(time());
                $password = sha1(rand(1000, 10000) . $user->getUsername() . $user->getSalt());
                $user->setResetPasswordHash($password);
                $this->om->persist($user);
                $this->om->flush();

                if ($this->mailManager->sendForgotPassword($user)) {
                    return array(
                        'user' => $user,
                        'form' => $form->createView()
                    );
                }

                return array(
                    'error' => $this->translator->trans('mail_config_problem', array(), 'platform'),
                    'form' => $form->createView()
                );
            }

            return array(
                'error' => $this->translator->trans('mail_not_exist', array(), 'platform'),
                'form' => $form->createView()
            );
        }

        return array(
            'error' => $this->translator->trans('wrong_captcha', array(), 'platform'),
            'form' => $form->createView()
        );
    }

    /**
     * @Route(
     *     "/sendmail",
     *     name="claro_redirect_to_reset",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     * @Template("ClarolineCoreBundle:Authentication:forgotPassword.html.twig")
     */
    public function redirectToResetAction(Request $request)
    {

        $lang = $request->query->get('lang');

        $uri = ($lang) ? $this->router->generate('claro_security_forgot_password', array('lang' => $lang)) : $this->router->generate('claro_security_forgot_password') ;

        return new RedirectResponse($uri);
    }

    /**
     * @Route(
     *     "/newpassword/{hash}/",
     *     name="claro_security_reset_password",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * @Template("ClarolineCoreBundle:Authentication:resetPassword.html.twig")
     */
    public function resetPasswordAction($hash)
    {
        $user = $this->userManager->getResetPasswordHash($hash);

        if (empty($user)) {
            return array(
                'error' => $this->translator->trans('url_invalid', array(), 'platform'),
            );
        }

        $form = $this->formFactory->create(FormFactory::TYPE_USER_RESET_PWD, array($this->translator), $user);
        $currentTime = time();

        // the link is valid for 24h
        if ($currentTime - (3600 * 24) < $user->getHashTime()) {
            return array(
                'hash' => $hash,
                'form' => $form->createView()
            );
        }

        return array('error' => $this->translator->trans('link_outdated', array(), 'platform'));
    }

    /**
     * @Route(
     *     "/validatepassword/{hash}",
     *     name="claro_security_new_password",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * @Template("ClarolineCoreBundle:Authentication:resetPassword.html.twig")
     */
    public function newPasswordAction($hash)
    {
        $user = $this->userManager->getResetPasswordHash($hash);
        $form = $this->formFactory->create(FormFactory::TYPE_USER_RESET_PWD, array($this->translator), $user);
        $form->handleRequest($this->request);

        // Get form data if valid
        if ($form->isValid() ) {
            $data = $form->getData();
            $plainPassword = $data->getPlainPassword();
        }

        // If form valid and password is complex enough
        if ($form->isValid() && $user->checkSolerniPassword($plainPassword)) {
            $user->setPlainPassword($plainPassword);
            $user->setResetPasswordHash(null);
            $this->om->persist($user);
            $this->om->flush();
            $this->request->getSession()
                ->getFlashBag()
                ->add('warning', $this->translator->trans('password_ok', array(), 'platform'));

            return new RedirectResponse($this->router->generate('claro_security_login'));
        }



        return array(
            'hash' => $hash,
            'form' => $form->createView(),
            'error' => $this->translator->trans('edit_password_error', array(), 'platform')
        );
    }

    /**
     * @Route("/authenticate.{format}")
     * @Method("POST")
     */
    public function postAuthenticationAction($format)
    {

        $formats = array('json', 'xml');

        if (!in_array($format, $formats)) {
            return new Response(
                "The format {$format} is not supported (supported formats are 'json', 'xml'",
                400
            );
        }

        $request = $this->request;
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $status = $this->authenticator->authenticate($username, $password) ? 200 : 403;
        $content = ($status === 403) ?
            array('message' => $this->translator->trans('login_failure', array(), 'platform')) :
            array();

        return $format === 'json' ?
            new JsonResponse($content, $status) :
            new XmlResponse($content, $status);
    }
}
