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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\BaseProfileType;
use Claroline\CoreBundle\Form\AccountValidatorType;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Library\HttpFoundation\XmlResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Claroline\CoreBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 */
class RegistrationController extends Controller
{
    private $request;
    private $userManager;
    private $configHandler;
    private $validator;
    private $roleManager;
    private $innerhash = '2)j766O6b22D8PR86#i260aRn21764N57F8W+50xy546M{x9tD74,148620E806s';

    /**
     * @DI\InjectParams({
     *     "request"       = @DI\Inject("request"),
     *     "userManager"   = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "validator"     = @DI\Inject("validator")
     * })
     */
    public function __construct(
        Request $request,
        UserManager $userManager,
        PlatformConfigurationHandler $configHandler,
        ValidatorInterface $validator,
        RoleManager $roleManager
    )
    {
        $this->request = $request;
        $this->userManager = $userManager;
        $this->configHandler = $configHandler;
        $this->validator = $validator;
        $this->roleManager = $roleManager;
    }
    /**
     * @Route(
     *     "/form",
     *     name="claro_registration_user_registration_form"
     * )
     *
     * @Template()
     *
     * Displays the user self-registration form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userRegistrationFormAction()
    {
        $this->checkAccess();
        
    	$data = array();
    	$session = $this->request->getSession();
        $user = new User();
        $localeManager = $this->get('claroline.common.locale_manager');
        $termsOfService = $this->get('claroline.common.terms_of_service_manager');
        
        $form = $this->get('form.factory')->create(new BaseProfileType($localeManager, $termsOfService), $user);

        if ($session->has("moocSession")) {
        	$moocSession = $session->get("moocSession");
        	$moocSession = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Mooc\MoocSession")->find($moocSession->getId());
        	$data['moocSession'] = $moocSession;
        }
        
        $data['form'] = $form->createView();
        
        return $data;
    }


/************************ START ******************************/

    /**
     * @Route(
     *     "/send_mail/{mail}/{hash}",
     *     name="claro_registration_send_mail"
     * )
     *
     * @Template("ClarolineCoreBundle:Registration:sendEmail.html.twig")
     *
     * Send an email with Key and instruction to validate account
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendEmailValidationAction($mail = null, $hash = null)
    {
        if( empty($mail) ){
            throw new AccessDeniedHttpException();
        }
        
        if( ! $hash ) {
            $hash = $this->getHash($mail);
        }

        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('ClarolineCoreBundle:User');
        $users = $userRepository->findByMail($mail); // get user

        if(empty($users)){
            throw new AccessDeniedHttpException();
        }

        $user = $users[0];

        $key = $user->getKeyValidate();
        $mail = $user->getMail();

        // Redirect to dashboard if user is already validated
        if ( $user->getIsValidate() ) {
            return $this->redirect($this->generateUrl('claro_desktop_open'));     
        }

        $log = $this->container->get('logger');
        $log->debug("Send mail with key {$key} to {$mail}");

        $this->userManager->sendEmailValidation($user);

        // But in the end it doesn't even matter
        return array('mail'=> $mail, 'hash' => $hash);
    }

    /**
     * @Route(
     *     "/validate_form/{mail}",
     *     name="claro_registration_validate_user_form"
     * )
     *
     * @Template("ClarolineCoreBundle:Registration:validEmailForm.html.twig")
     *
     * Let user validate its account with key from mail sended
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validateUserFormAction($mail) {
        $log = $this->container->get('logger');
        $log->debug("Calling Formulaire manualy");

        $request = $this->get('request');
        $account_validator_form = $request->request->get('account_validator_form');
        
        $key = null;
        if( !empty($account_validator_form)){
            $key = $account_validator_form['keyValidate'];           
        }
        
        $log->debug(" mail = {$mail} && key = {$key}");

        $msg = 'accountValidation_key_empty';
        $log->debug("Prepare Form Creation");

        $user = new User();
        if(!empty($mail)){
            $user->setMail($mail);
        }
        $user->setKeyValidate(null);

        $form = $this->get('form.factory')
                     ->create(new AccountValidatorType($user));
        $form = $form->createView();

        return array('user'=> $user, 
                    'msg' => $msg, 
                    'form' => $form, 
                    'mail' => $mail, 
                    'hash' => $this->getHash($mail),
                    'key' => $key
                );
    }

    /**
     * @Route(
     *     "/validate_form/{mail}/{key}",
     *     name="claro_registration_validate_user"
     * )
     *
     * @Template("ClarolineCoreBundle:Registration:validEmailForm.html.twig")
     *
     * Let user validate its account with key from mail sended
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validateUserAction(Request $request, $mail, $key) {
        
        $form = null;
        $log = $this->container->get('logger');

        $log->debug("Trying validation {$mail} with key {$key}");
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('ClarolineCoreBundle:User');
        $users = $userRepository->findByMail($mail); // get user

        $userDb = null;
        $user = null;
        if(!empty($users)){
            $userDb = $users[0];
        }

        $nextUrl = '';

        if($userDb == null || $userDb->getKeyValidate() !== $key){

            $log->debug("key {$key} not valid for mail {$mail}");
            $msg = 'accountValidation_key_ko';

            $user = new User();
            if(!empty($mail)){
                $user->setMail($mail);
            }
            $user->setKeyValidate(null);
            
            /*
            $form = $this->get('form.factory')
                         ->create(new AccountValidatorType($user));
            $form = $form->createView();
            */
        } elseif ( $userDb->getIsValidate() ) {
            $msg = 'accountValidation_key_already';
            $nextUrl = $this->get('router')->generate('claro_desktop_open_tool', array('toolName' => 'home'), true);
        } else {
            $msg = 'accountValidation_key_ok';
            //Save a valid user
            $userDb->setIsValidate(true);
            $em->persist($userDb);
            $em->flush();
            $log->debug("Auto-validation {$mail} with success with key {$key}");
            $token = new UsernamePasswordToken($userDb, null, 'main', $userDb->getRoles());
            $this->get('security.context')->setToken($token);
            $this->get('session')->set('_security_main',serialize($token));
            //Send post-validation mail
            $this->userManager->sendEmailValidationConfirmee($userDb);
            //Generate next url
            $session = $this->request->getSession();
            if ( $session->has('moocSession') ) {
            	$moocSession = $session->get('moocSession'); 
            	$nextUrl = $this->get('router')->generate('session_subscribe', array ( 'sessionId' => $moocSession->getId() ));
                $session->remove('moocSession');
            } elseif ( $session->has('privateMoocSession') ) {
                $moocSession = $session->get('privateMoocSession');
                $nextUrl = $this->get('router')->generate('mooc_view', array ( 'moocId' => $moocSession->getMooc()->getId(), 'moocName' => $moocSession->getMooc()->getTitle() ));
                $session->remove('privateMoocSession');
            // Finish mooc notification    
            } elseif ($request->query->has('moocId')) {
                $moocRepository = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Mooc\\MoocSession');
                $moocSession = $moocRepository->getActiveOrLastSessionForMoocId($request->query->get('moocId'));

                $nextUrl = $this->get('router')->generate('session_subscribe', array ( 'sessionId' => $moocSession->getId() ));
            } else {
                $nextUrl = $this->get('router')->generate('claro_desktop_open_tool', array('toolName' => 'home'), true);
            }
        }

        return array(
            'user'=> $user, 
            'msg' => $msg, 
            // 'form' => $form, 
            'mail' => $mail, 
            'hash' => $this->getHash($mail),
            'key' => $key,
            'nextUrl' => $nextUrl
        );
    }

/************************ END ******************************/

    /**
     * @Route(
     *     "/create",
     *     name="claro_registration_register_user"
     * )
     *
     * @Template("ClarolineCoreBundle:Registration:userRegistrationForm.html.twig")
     *
     * Registers a new user and displays a flash message in case of success.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerUserAction()
    {
        $this->checkAccess();
        $session = $this->request->getSession();
        $data = array();
        $user = new User();

        $localeManager = $this->get('claroline.common.locale_manager');
        $termsOfService = $this->get('claroline.common.terms_of_service_manager');
        /* @var $form Form */
        $form = $this->get('form.factory')->create(new BaseProfileType($localeManager, $termsOfService), $user);

        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $this->roleManager->setRoleToRoleSubject($user, $this->configHandler->getParameter('default_role'));

            /* @var $request Request */
            $request = $this->get('request');
            $user->setLocale($request->getLocale());
            $this->get('claroline.manager.user_manager')->createUserWithRole(
                $user,
                PlatformRoles::USER
            );

            $data['mail'] = $user->getMail();
            $data['hash'] = $this->getHash($user->getMail());
            return $this->redirect($this->generateUrl('claro_registration_send_mail', $data));
        } else {
            if ($session->has("moocSession")) {
                $moocSession = $session->get("moocSession");
                $moocSession = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Mooc\MoocSession")->find($moocSession->getId());
                $data['moocSession'] = $moocSession;
            }

            if ($session->has("privateMoocSession")) {
                $moocSession = $session->get("privateMoocSession");
                $moocSession = $this->getDoctrine()->getRepository("ClarolineCoreBundle:Mooc\MoocSession")->find($moocSession->getId());
                $data['privateMoocSession'] = $moocSession;
            }

            $data['form'] = $form->createView();

            return $data;
        }
    }

    /**
     * @Route(
     *     "/api/csrf",
     *     name="claro_registration_register_csrf_json"
     * )
     *
     * Gets a CSRF token by JSON
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCSRFApiAction()
    {
        $this->checkAccess();
        /* @var $csrf CsrfTokenManagerInterface */
        $csrf = $this->get('security.csrf.token_manager');
        $token = $csrf->refreshToken("profile_form");

        return new Response(json_encode(array('token' => $token->getValue())));
    }

    /**
     * @Route(
     *     "/api/create",
     *     name="claro_registration_register_user_json"
     * )
     *
     * Registers a new user and sends back JSON.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerUserApiAction()
    {
        $this->checkAccess();
        $session = $this->request->getSession();
        $data = array();
        $user = new User();

        $localeManager = $this->get('claroline.common.locale_manager');
        $termsOfService = $this->get('claroline.common.terms_of_service_manager');
        /* @var $form Form */
        $form = $this->get('form.factory')->create(new BaseProfileType($localeManager, $termsOfService), $user);


        $request = $this->get('request');

        $form->handleRequest($request);

        $errors = array();
        if ($form->isValid()) {
            $status = "ok";
            $returnCode = 200;
            $this->roleManager->setRoleToRoleSubject($user, $this->configHandler->getParameter('default_role'));
            $this->get('claroline.manager.user_manager')->createUserWithRole(
                $user,
                PlatformRoles::USER
            );

            $key = $user->getKeyValidate();
            $mail = $user->getMail();

            $log = $this->container->get('logger');
            $log->debug("Send mail with key {$key} to {$mail}");

            if ($request->request->has('moocId')) {
                $moocId = $request->request->get('moocId');
            } else {
                $moocId = null;
            }
            $this->userManager->sendEmailValidation($user, $moocId);
        } else {
            $status = "ko";
            $returnCode = 400;

            $this->getErrors($form, $errors);
        }
        return new Response(json_encode(array('status' => $status, 'errors' => $errors)), $returnCode);
    }

    private function getErrors(Form $form, &$errors)
    {
        foreach ($form->getErrors() as $error) {
            $errors[$form->getName()] = $error->getMessage();
        }
        foreach ($form->all() as $child) {
            $this->getErrors($child, $errors);
        }
    }

    private function getHash($mail){
        return md5($mail.$this->innerhash);
    }

    /**
     * @Route("/new/user.{format}", name = "claro_register_user")
     * @Method({"POST"})
     */
    public function postUserRegistrationAction($format)
    {
        $formats = array('json', 'xml');

        if (!in_array($format, $formats)) {
            Return new Response(
                "The format {$format} is not supported (supported formats are 'json', 'xml')",
                400
            );
        }

        $status = 200;
        $content = array();

        if ($this->configHandler->getParameter('allow_self_registration')) {
            $request = $this->request;

            $user = new User();
            $user->setUsername($request->request->get('username'));
            $user->setPlainPassword($request->request->get('password'));
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setMail($request->request->get('mail'));
            $user->setFacebookAccount(false);

            $errorList = $this->validator->validate($user);

            if (count($errorList) > 0) {
                $status = 422;
                foreach ($errorList as $error) {
                    $content[] = array('property' => $error->getPropertyPath(), 'message' => $error->getMessage());
                }
            } else {
                $this->userManager->createUser($user);
            }
        } else {
            $status = 403;
        }

        return $format === 'json' ?
            new JsonResponse($content, $status) :
            new XmlResponse($content, $status);
    }

    /**
     * Checks if a user is allowed to register.
     * ie: if the self registration is disabled, he can't.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Respone
     *
     */
    private function checkAccess()
    {
        $securityContext = $this->get('security.context');
        $configHandler = $this->get('claroline.config.platform_config_handler');
        $isSelfRegistrationAllowed = $configHandler->getParameter('allow_self_registration');

        if (!$securityContext->getToken()->getUser() instanceof User && $isSelfRegistrationAllowed) {
            return;
        }

        throw new AccessDeniedHttpException();
    }
}
