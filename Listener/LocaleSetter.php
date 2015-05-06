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

use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;

/**
 * @Service
 *
 * Listener setting the platform language according to platform_options.yml.
 */
class LocaleSetter
{
    private $localeManager;
    private $container;  
    private $request;
    private $translator;
    /**
     * @InjectParams({
     *     "localeManager"  = @Inject("claroline.common.locale_manager"),
     *     "container"      = @DI\Inject("service_container"),
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "translator"     = @DI\Inject("translator")
     * })
     */
    public function __construct(
            LocaleManager $localeManager, 
            ContainerInterface $container,
            RequestStack $requestStack,
            Translator $translator
            )
    {
        $this->localeManager = $localeManager;
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
    }

    /**
     * @Observe("kernel.request")
     *
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $token = $this->container->get('security.context')->getToken();
        $session = $this->container->get('session');
        // Do we have a query ? It means we want to change language
        $lang = $request->query->get('lang');
        
        // Create the available local in a numeric keys array 
        $availableLocales = array();
        if ( $this->localeManager->getAvailableLocales() ) {
            foreach ( $this->localeManager->getAvailableLocales() as $availableLocale ) {
                $availableLocales[] = $availableLocale;
            }
        }

        // If we have query, delete language list in session and change language
        if ( $lang && in_array( $lang, $availableLocales )) {
            if ( $session->has('availableLanguages') ) {
               $session->remove('availableLanguages');
            }
            $request->setLocale($lang);
            $this->localeManager->setSessionLanguageList($availableLocales);
            
            // If the user is connected, change user settings
            if ( $token instanceof \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken ) {
                $user = $token->getUser();
                $user->setLocale($lang);
                $doctrine = $this->container->get('doctrine.orm.entity_manager');
                $doctrine->merge($user);
                $doctrine->flush();
            }
            return;
        }

        // If not - and we do not have a previous $lang query, it's a regular anon user with default language
        if ( ! $session->has('availableLanguages') && $token instanceof \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken ) {           
            /* If anon. user, either serve the browser langage or if not applicable, the platform langage */
            if ( $request->getPreferredLanguage( $availableLocales ) ) {
                $request->setLocale( $request->getPreferredLanguage( $availableLocales ) );
            } else {
                $request->setLocale( $this->localeManager->getDefaultLocale() );
            }
            $this->localeManager->setSessionLanguageList($availableLocales);
            return;
        }
        
        // Use user settings
        $locale = $this->localeManager->getUserLocale($request);
        $request->setLocale($locale);
    }
}