<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\TermsOfServiceType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\Session;
/**
 * @DI\Service("claroline.authentication_handler")
 */
class AuthenticationSuccessListener implements AuthenticationSuccessHandlerInterface
{
    private $securityContext;
    private $eventDispatcher;
    private $configurationHandler;
    private $templating;
    private $formFactory;
    private $termsOfService;
    private $manager;
    private $router;
    private $session;

    private $breakChain = false;

    /**
     * @DI\InjectParams({
     *     "context"                = @DI\Inject("security.context"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configurationHandler"   = @DI\Inject("claroline.config.platform_config_handler"),
     *     "templating"             = @DI\Inject("templating"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "termsOfService"         = @DI\Inject("claroline.common.terms_of_service_manager"),
     *     "manager"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"                 = @DI\Inject("router"),
     *     "session"                = @DI\Inject("session")
     * })
     *
     */
    public function __construct(
        SecurityContextInterface $context,
        StrictDispatcher $eventDispatcher,
        PlatformConfigurationHandler $configurationHandler,
        EngineInterface $templating,
        FormFactory $formFactory,
        TermsOfServiceManager $termsOfService,
        ObjectManager $manager,
        Router $router,
        Session $session
    )
    {
        $this->securityContext = $context;
        $this->eventDispatcher = $eventDispatcher;
        $this->configurationHandler = $configurationHandler;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->termsOfService = $termsOfService;
        $this->manager = $manager;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @DI\Observe("security.interactive_login")
     */
    public function onLoginSuccess(InteractiveLoginEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();

        $this->eventDispatcher->dispatch('log', 'Log\LogUserLogin', array($user));
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->securityContext->setToken($token);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $this->securityContext->getToken()->getUser();
        
        // Finish mooc session subscription
        if( $this->session->has('moocSession') ) {
        	$moocSession = $this->session->get('moocSession');
            $uri = $this->router->generate('session_subscribe', array ( 'sessionId' => $moocSession->getId() ));
            $this->session->remove('moocSession');
        // Finish private mooc authentification 
        } elseif ( $this->session->has('privateMoocSession') ) {  
        	$moocSession = $this->session->get('privateMoocSession');
            $uri = $this->router->generate('mooc_view', array ( 'moocId' => $moocSession->getMooc()->getId(), 'moocName' => $moocSession->getMooc()->getTitle() ));
            $this->session->remove('privateMoocSession');
        // Finish mooc notification    
        } elseif ( $this->session->has('moocNotification') ) {
            $uri = $this->session->get('moocNotification');
            $this->session->remove('moocNotification');
        // Redirect if last page is activated
        } elseif ($this->configurationHandler->getParameter('redirect_after_login') && $user->getLastUri() !== null) {
            $uri = $user->getLastUri();
        } else {
            $uri = $this->router->generate('claro_desktop_open');
        }

        $response = new RedirectResponse($uri);

        return $response;
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if(!$this->breakChain){
            $event = $this->isAValidAccount($event);    
        }
        if(!$this->breakChain){
            $event = $this->showTermOfServices($event);
        }

    }

    /**
     * @DI\Observe("kernel.response")
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->saveLastUri($event);
    }

    public function saveLastUri(FilterResponseEvent $event)
    {
        if ($this->configurationHandler->getParameter('redirect_after_login')) {

            if ($event->isMasterRequest()
                && !$event->getRequest()->isXmlHttpRequest()
                && !in_array($event->getRequest()->attributes->get('_route'), $this->getExcludedRoutes())
                && 'GET' === $event->getRequest()->getMethod()
                && 200 === $event->getResponse()->getStatusCode()
                && !$event->getResponse() instanceof StreamedResponse
            ) {
                if ($token =  $this->securityContext->getToken()) {
                    if ('anon.' !== $user = $token->getUser()) {
                        $uri = $event->getRequest()->getRequestUri();
                        $user->setLastUri($uri);
                        $this->manager->persist($user);
                        $this->manager->flush();
                    }
                }
            }

        }
    }

    private function isAValidAccount(GetResponseEvent $event){

        $authorizedUrl = array(
                    'claro_registration_send_mail',
                    'claro_registration_validate_user_form', 
                    'claro_registration_validate_user',
                    'orange_cms_api_user',
                    'orange_cms_api_route',
                    'orange_cms_api_moocs',
                    'orange_search',
                    'solerni_catalogue',
            );

        ;

        //Case of FB account
        if ($event->isMasterRequest() and
            $user = $this->getUser($event->getRequest()) and
            !$user->getIsValidate() && 
            $user->isFacebookAccount()) {
        	
            $user->setIsValidate(true);
            $this->manager->persist($user);
            $this->manager->flush();
            return $event;
        }

        if ($event->isMasterRequest() and
            $user = $this->getUser($event->getRequest()) and
            !$user->getIsValidate() and
            !$this->isImpersonated() and 
            !in_array($event->getRequest()->attributes->get('_route'), $authorizedUrl)) {

            $uri = $this->router->generate('claro_registration_send_mail', array('userId' => $user->getId()));
            $response = new RedirectResponse($uri);
            $event->setResponse($response);
            $this->breakChain = true;
        }
        return $event;
    }

    private function showTermOfServices(GetResponseEvent $event)
    {
        if ($event->isMasterRequest() and
            $this->configurationHandler->getParameter('terms_of_service') and
            $user = $this->getUser($event->getRequest()) and
            !$user->hasAcceptedTerms() and
            !$this->isImpersonated()and
            $content = $this->termsOfService->getTermsOfService(false)
        ) {
            if ($termsOfService = $event->getRequest()->get('accept_terms_of_service_form') and
                isset($termsOfService['terms_of_service'])
            ) {
                $user->setAcceptedTerms(true);
                if ( isset($termsOfService['com_terms_of_service']) ) {
                    $user->setAcceptedComTerms($termsOfService['com_terms_of_service']);
                } else {
                    $user->setAcceptedComTerms(false);
                }
                $this->manager->persist($user);
                $this->manager->flush();
            } else {
                $form = $this->formFactory->create(new TermsOfServiceType(), $content);
                $response = $this->templating->render(
                    "ClarolineCoreBundle:Authentication:termsOfService.html.twig",
                    array('form' => $form->createView())
                );

                $event->setResponse(new Response($response));
            }
        }

        return $event;
    }

    /**
     * Return a user if need to accept the terms of service
     *
     * @return Claroline\CoreBundle\Entity\User
     */
    private function getUser($request)
    {
        if ($request->get('_route') !== 'claroline_locale_change' and
            $request->get('_route') !== 'claroline_locale_select' and
            $request->get('_route') !== 'bazinga_exposetranslation_js' and
            $token = $this->securityContext->getToken() and
            $user = $token->getUser() and
            $user instanceof User
        ) {
            return $user;
        }
    }

    private function getExcludedRoutes()
    {
        return array(
            'bazinga_exposetranslation_js',
            'login_check',
            'login',
            'orange_cms_api_user',
            'orange_cms_api_route',
            'orange_cms_api_moocs',
        );
    }

    public function isImpersonated()
    {
        if ($this->securityContext->isGranted('ROLE_PREVIOUS_ADMIN')) {
            foreach ($this->securityContext->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    return true;
                }
            }
        }
    }
}
