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
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @Service
 *
 * Listener setting the platform language according to platform_options.yml.
 */
class LocaleSetter
{
    private $localeManager;
    private $container;  
    /**
     * @InjectParams({
     *     "localeManager"  = @Inject("claroline.common.locale_manager"),
     *     "container"      = @DI\Inject("service_container")
     * })
     */
    public function __construct(
            LocaleManager $localeManager, 
            ContainerInterface $container
            )
    {
        $this->localeManager = $localeManager;
        $this->container = $container;
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
        
        if ( $token instanceof \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken ) {
            /* If anon. user, either serve the browser langage or if not applicable, the platform langage */
            if ( $request->getPreferredLanguage( $this->localeManager->getAvailableLocales() ) ) {
                $request->setLocale( $request->getPreferredLanguage( $this->localeManager->getAvailableLocales() ) );
            } else {
                $request->setLocale( $this->localeManager->getDefaultLocale() );
            }
            return;
        }
        
        $locale = $this->localeManager->getUserLocale($request);
        $request->setLocale($locale);
    }
}
