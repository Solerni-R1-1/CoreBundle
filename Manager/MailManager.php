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

use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\CacheWarmerInterface;
use Claroline\CoreBundle\Event\RefreshCacheEvent;
use Claroline\CoreBundle\Persistence\ObjectManager;


/**
 * @DI\Service("claroline.manager.mail_manager")
 */
class MailManager
{
    private $router;
    private $mailer;
    private $translator;
    private $container;
    private $ch;
    private $cacheManager;
    private $contentManager;
    private $om;

    /**
     * @DI\InjectParams({
     *     "router"         = @DI\Inject("router"),
     *     "mailer"         = @DI\Inject("mailer"),
     *     "ch"             = @DI\Inject("claroline.config.platform_config_handler"),
     *     "container"      = @DI\Inject("service_container"),
     *     "cacheManager"   = @DI\Inject("claroline.manager.cache_manager"),
     *     "contentManager" = @DI\Inject("claroline.manager.content_manager"),
     * "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */



    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        Translator $translator,
        PlatformConfigurationHandler $ch,
        ContainerInterface $container,
        CacheManager $cacheManager,
        ContentManager $contentManager,
        ObjectManager $om

    )
    {
        $this->router = $router;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->container = $container;
        $this->ch = $ch;
        $this->cacheManager = $cacheManager;
        $this->contentManager = $contentManager;
        $this->om = $om;
    }


    /**
     * @return boolean
     */
    public function isMailerAvailable()
    {
        return $this->cacheManager->getParameter('is_mailer_available');
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return boolean
     */
    public function sendForgotPassword(User $user)
    {
        $hash = $user->getResetPasswordHash();
        $link = $this->router->generate('claro_security_reset_password', array('hash' => $hash), true);

        $subject = $this->translator->trans('init_password_title', array(), 'mail');
        $body = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Mail:forgotPassword.html.twig', array('user' => $user, 'link' => $link)
        );

        return $this->send($subject, $body, array($user));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return boolean
     */
    public function sendValidationMessage(User $user, $moocId = null)
    {
        $subject = $subject = $this->translator->trans('sub_email_title', array(), 'mail');
        $body = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Registration:emailValidation.html.twig',  array('user' => $user, 'moocId' => $moocId));


        return $this->send($subject, $body, array($user));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return boolean
     */
    public function sendValidationConfirmeeMessage(User $user){

        $subject = $this->translator->trans('sub_confirm_title', array(), 'mail');
        $body = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Registration:emailValidationConfirmee.html.twig',  array('user' => $user));


        return $this->send($subject, $body, array($user));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param \Claroline\CoreBundle\Entity\Mooc\MoocSession $session
     *
     * @return boolean
     */
    public function sendInscriptionMoocMessage(User $user, MoocSession $session){

        $subject = $this->translator->trans('sub_mooc_title', array(), 'mail').' '.$session->getMooc()->getTitle();
        $body = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Registration:emailInscriptionMooc.html.twig',  array('user' => $user, 'session' => $session));

        return $this->send($subject, $body, array($user));
    }

    /**
     * @param User $user
     *
     * @return boolean
     */
    public function sendCreationMessage(User $user)
    {
        $subject = $this->translator->trans('sub_welcome_title', array(), 'mail');
        $body = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Registration:emailRegistration.html.twig',  array('user' => $user));


        return $this->send($subject, $body, array($user));
    }

    /**
     * @param User $user
     *
     * @return boolean
     */
    public function sendChangePasswordMessage(User $user)
    {
        $subject = $this->translator->trans('pass_change_confirm_title', array(), 'mail');
        $body = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Registration:emailChangePassword.html.twig',  array('user' => $user));


        return $this->send($subject, $body, array($user));
    }


    public function getMailInscription()
    {
        return $this->contentManager->getContent(array('type' => 'claro_mail_registration'));
    }

    public function getMailLayout()
    {
        return $this->contentManager->getContent(array('type' => 'claro_mail_layout'));
    }

    /**
     * @param string $subject
     * @param string $body
     * @param User[] $users
     * @param User   $from
     * @param User   $replyTo
     *
     * @return boolean
     */
    public function send($subject, $body, array $users, $from = null, $replyTo = null)
    {
        if ($this->isMailerAvailable()) {
            $to = [];

            $layout = $this->contentManager->getTranslatedContent(array('type' => 'claro_mail_layout'));

            $fromEmail = ($from === null) ? $this->ch->getParameter('support_email') : $from->getMail();
            $replyToEmail = ($replyTo === null) ? null : $replyTo->getMail();
            $locale = count($users) === 1 ? $users[0]->getLocale() : $this->ch->getParameter('locale_language');

            if (!$locale) {
                $locale = $this->ch->getParameter('locale_language');
            }

            //$usedLayout = $layout[$locale]['content'];

            //$body = str_replace('%content%', $body, $usedLayout);

            $body = str_replace('%platform_name%', $this->ch->getParameter('name'), $body);

            if ($from) {
                $body = str_replace('%first_name%', $from->getFirstName(), $body);
                $body = str_replace('%last_name%', $from->getLastName(), $body);
            } else {
                $body = str_replace('%first_name%', $this->ch->getParameter('name'), $body);
                $body = str_replace('%last_name%', '', $body);
            }


            foreach ($users as $user) {

                $mail = $user->getMail();

                if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    $to[] = $mail;
                }
            }

            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($fromEmail)
                ->setBody($body, 'text/html');

            if (count($to) > 1) {
                $message->setBcc($to);
            } else {
                $message->setTo($to);
            }

            if ($replyToEmail !== null) {
                $message->setReplyTo($replyToEmail);
            }


            return $this->mailer->send($message) ? true : false;
        }

        return false;
    }

    /**
     * @param $translatedContents
     *
     * @return array
     */
    public function validateInscriptionMail(array $translatedContents)
    {
        $languages = array_keys($translatedContents);
        $errors = array();

        foreach ($languages as $language) {
            if (!strpos($translatedContents[$language]['content'], '%password%')) {
                $errors[$language]['content'][] = 'missing_%password%';
            }
        }

        return $errors;
    }

    /**
     * @param $translatedContents
     *
     * @return array
     */
    public function validateLayoutMail(array $translatedContents)
    {
        $languages = array_keys($translatedContents);
        $errors = array();

        foreach ($languages as $language) {
            if (!strpos($translatedContents[$language]['content'], '%content%')) {
                $errors[$language]['content'] = 'missing_%content%';
            }
        }

        return $errors;
    }

    /**
     * @DI\Observe("refresh_cache")
     */
    public function refreshCache(RefreshCacheEvent $event)
    {
        try {
            $this->mailer->getTransport()->start();
            $event->addCacheParameter('is_mailer_available', true);
        } catch (\Swift_TransportException $e) {
            $event->addCacheParameter('is_mailer_available', false);
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return boolean
     */
    public function sendContactMessage($contactName, $contactMail, $replyTo, $data, $content){

        $subject = 'Demande de contact Solerni au service '.$contactName;
        $body = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Contact:emailContact.html.twig',
            array('contactName' => $contactName,
                'replyTo' => $replyTo,
                'data' => $data,
                'content' => $content));

        $userSolerni = new User();
        $userSolerni->setMail($contactMail);

        $userToReply = new User();
        $userToReply->setMail($replyTo);

        return $this->send($subject, $body, array($userSolerni), null, $userToReply);
    }

    public function getSupportMail(){
        return $this->ch->getParameter('support_email');
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return boolean
     */
    public function sendNotificationMessage(User $userCible = null, $notifType,  MoocSession $mooc = null, User $userAction = null, $titleDispositif = null, $lien = null)
    {

//        print_r($notifType);
//        echo ("<br/>");
//       // print_r($mooc->getMooc()->getTitle());
//        echo ("<br/>");
//        print_r($userAction);
//        echo ("<br/>");
//        print_r($titleDispositif);
//        echo ("<br/>");
//        print_r($lien);
//
//
//        echo ("<br/>");
//       // print_r("article : " +$mooc->getMooc()->getNotifarticlemooc());
//        echo ("<br/>");
//
//     //   print_r("sujettheme : " +$mooc->getMooc()->getNotifsujetthememooc());
//        echo ("<br/>");
//
//      //  print_r("citer : " +$mooc->getMooc()->getNotifcitermooc());
//        echo ("<br/>");
//
//    //    print_r("like : " +$mooc->getMooc()->getNotiflikemooc());
//        echo ("<br/>");

      $nomDispositif =   $mooc->getMooc()->getTitle();
        $titlemooc = $nomDispositif;

        if ($notifType=="article" && $mooc->getMooc()->getNotifarticlemooc() == 1 ){

            $subject = $titlemooc.' : '. $this->translator->trans('notif_title_article', array(), 'mail');

            foreach($this->om->getRepository('Claroline\CoreBundle\Entity\User')->getAllUsersForExport($mooc) as $username) {

                $user = new User();
                $user->setId($username['id']);
                $user->setUsername($username['username']);
                $user->setFirstName($username['firstname']);
                $user->setLastName($username['lastname']);
                $user->setLocale($username['locale']);
                $user->setMail($username['mail']);

                /*    print_r($username['locale']);
                    print_r("<br/>");
                    print_r($username['username']);
                    print_r("<br/>");
                    print_r($username['firstname']);
                    print_r("<br/>");
                    print_r($username['lastname']);
                    print_r("<br/>");
                    print_r($username['mail']);


                    print_r("<br/>");
                    print_r($username['notifarticle']);
                    print_r("<br/>");
                    print_r($username['notifsujettheme']);
                    print_r("<br/>");
                    print_r($username['notifciter']);
                    print_r("<br/>");
                    print_r($username['notiflike']);
                    print_r("<br/>");


                 die();*/


                if ($username['notifarticle'] == 1) {

                    $body = $this->container->get('templating')->render(
                        'ClarolineCoreBundle:Notification:notification_template.html.twig', array('user' => $user, 'notifType' => $notifType, 'userAction' => $userAction, 'titleDispositif' => $nomDispositif, 'lien' => $lien));

                    $this->send($subject, $body, array($user));
                }
            }
            return true;
        }else if($notifType == "sujet" && $mooc->getMooc()->getNotifsujetthememooc() == 1){

            $subject = $titlemooc.' : '. $this->translator->trans('notif_title_sujet', array(), 'mail');

            foreach($this->om->getRepository('Claroline\CoreBundle\Entity\User')->getAllUsersForExport($mooc) as $username) {

                $user = new User();
                $user->setId($username['id']);
                $user->setUsername($username['username']);
                $user->setFirstName($username['firstname']);
                $user->setLastName($username['lastname']);
                $user->setLocale($username['locale']);
                $user->setMail($username['mail']);

                if ($username['notifsujettheme'] == 1) {

                    $body = $this->container->get('templating')->render(
                        'ClarolineCoreBundle:Notification:notification_template.html.twig', array('user' => $user, 'notifType' => $notifType, 'userAction' => $userAction, 'titleDispositif' => $nomDispositif, 'lien' => $lien));

                    $this->send($subject, $body, array($user));
                }
            }
            return true;
        }else if($notifType == "theme" && $mooc->getMooc()->getNotifsujetthememooc() == 1 ){

            $subject = $titlemooc.' : '. $this->translator->trans('notif_title_theme', array(), 'mail');

            foreach($this->om->getRepository('Claroline\CoreBundle\Entity\User')->getAllUsersForExport($mooc) as $username) {

                $user = new User();
                $user->setId($username['id']);
                $user->setUsername($username['username']);
                $user->setFirstName($username['firstname']);
                $user->setLastName($username['lastname']);
                $user->setLocale($username['locale']);
                $user->setMail($username['mail']);

                if ($username['notifsujettheme'] == 1) {

                    $body = $this->container->get('templating')->render(
                        'ClarolineCoreBundle:Notification:notification_template.html.twig', array('user' => $user, 'notifType' => $notifType, 'userAction' => $userAction, 'titleDispositif' => $nomDispositif, 'lien' => $lien));

                    $this->send($subject, $body, array($user));
                }
            }
            return true;
        }else if($notifType == "citation" ){

            if ($mooc->getMooc()->getNotifcitermooc() == 1) {

                $subject = $titlemooc . ' : ' . $this->translator->trans('notif_title_citation', array(), 'mail');

                $body = $this->container->get('templating')->render(
                    'ClarolineCoreBundle:Notification:notification_template.html.twig', array('user' => $userCible, 'notifType' => $notifType, 'userAction' => $userAction, 'titleDispositif' => $nomDispositif, 'lien' => $lien));


                if (($userCible->getNotifciter() == 1)) {

                    $this->send($subject, $body, array($userCible));
                }
            }
            return true;
        } else if ($notifType == "like" ){
            if ($mooc->getMooc()->getNotiflikemooc() == 1) {

                $subject = $titlemooc . ' : ' . $this->translator->trans('notif_title_like', array(), 'mail');

                $body = $this->container->get('templating')->render(
                    'ClarolineCoreBundle:Notification:notification_template.html.twig', array('user' => $userCible, 'notifType' => $notifType, 'userAction' => $userAction, 'titleDispositif' => $nomDispositif, 'lien' => $lien));

                if ($userCible->getNotiflike() == 1) {
                    $this->send($subject, $body, array($userCible));
                }
            }
            return true;
        }
        return true;
    }

}
