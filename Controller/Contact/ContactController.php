<?php


namespace Claroline\CoreBundle\Controller\Contact;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Claroline\CoreBundle\Manager\MailManager;
use Icap\LessonBundle\Entity\Lesson;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Entity\Contact\Contact;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Contact\ContactManager;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Claroline\CoreBundle\Library\Security\Utilities;


/**
 * Description of StaticController
 *
 * @author Kevin Danezis <kdanezis@sii.fr>
 * 
 * @copyright 2014 @ sii.fr for Orange
 *           
 */
class ContactController extends Controller
{    
    private $request;
    private $translator;
    private $security;
    private $router;
    private $mailManager;
    private $contactManager;
    private $formFactory;
    private $utils;
    
    
    /**
     * @DI\InjectParams({
     *     "request"            = @DI\Inject("request"),
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "translator"         = @DI\Inject("translator"),
     *     "mailManager"        = @DI\Inject("claroline.manager.mail_manager"),
     *     "contactManager"     = @DI\Inject("claroline.manager.contactManager"),
     *     "utils"              = @DI\Inject("claroline.security.utilities")
     * })
     */
    public function __construct( 
        	Request $request,
            SecurityContextInterface $security, 
            UrlGeneratorInterface $router, 
        	FormFactory $formFactory,
            TranslatorInterface $translator,
            MailManager $mailManager,
            ContactManager $contactManager,
            Utilities $utils
        ) {
        $this->request = $request;
        $this->translator = $translator;
        $this->security = $security;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->mailManager = $mailManager;
        $this->contactManager = $contactManager;
        $this->utils = $utils;
    }


    /**
     * @Route("/contact", name="contact_show")
     */
    public function showAction() {
        $token = $this->security->getToken();
        $loggedUser = $token->getUser();
        $roles = $this->utils->getRoles($token);

        $isLogged = false;
        if (!in_array('ROLE_ANONYMOUS', $roles)) {
            $isLogged = true;
        }

    	$defaultServiceName = 'contact_general';

        $contactsCollection = $this->contactManager->getAllContacts();
        $contacts = array();
        $contactsMail = array();
        if(empty($contactsCollection)){
        	$contacts["-1"] = $defaultServiceName;
        } else {
        	foreach ($contactsCollection as $contact) {
	        	$contacts[$contact->getId()] = $contact->getName();
	        	$contactsMail[$contact->getId()] = $contact->getMail();
	        }

        }

        $civilite = array(
                    $this->translator->trans('contact_form_civil_monsieur', array(), 'platform'),
                    $this->translator->trans('contact_form_civil_madame', array(), 'platform'),
                );
        
    	$form = $this->formFactory->create(FormFactory::TYPE_CONTACT, array($this->translator, $contacts, $civilite, $loggedUser));
        $form->handleRequest($this->request);
        $message = null;
        $formView = null;

        // Formulaire complété
        if ($form->isValid()) {
        	$data = $form->getData();

            //Pour sécurité
            if($isLogged){
                $data['replyTo'] = $loggedUser->getMail();
                $data['prenom'] = $loggedUser->getFirstName();
                $data['nom'] = $loggedUser->getLastName();
            }

        	$contactId = $data['contact'];
        	$replyTo = $data['replyTo'];
        	$content = $data['content'];
            if(isset($data['civilite'])) {
                $data['civilite'] = $civilite[$data['civilite']];
            }
            unset($data['contact']);
            unset($data['replyTo']);
            unset($data['content']);

        	$contactName = $this->translator->trans($defaultServiceName, array(), 'platform');
        	$contactMail = $this->mailManager->getSupportMail();
        	
        	if($contactId !== -1 && $contactId !== null){
        		$contactName = $contacts[$contactId];
        		$contactMail = $contactsMail[$contactId];
        	}

			$this->mailManager->sendContactMessage($contactName, $contactMail, $replyTo, $data, $content);

			$message = $this->translator->trans('contact_success', array(), 'platform');
        } else {
            $formView = $form->createView();
        }

        return $this->render(
            'ClarolineCoreBundle:Contact:contact.html.twig',
            array(
               'form' => $formView,
               'message' => $message
            )
        );

        
    }

    /**
     * @Route("/contact/services/edit", name="contact_edit_services")
     *
     * @SEC\PreAuthorize("hasRole('ADMIN')")
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editServicesAction(User $loggedUser) {

        if(!$loggedUser->hasRole('ROLE_ADMIN')){
            throw new AccessDeniedException("Access denied");
        }

    	$contactsCollection = $this->contactManager->getAllContacts();
    	$formArray = array();

    	foreach ($contactsCollection as $contactService) {
    		$form = $this->formFactory->create(FormFactory::TYPE_CONTACT_SERVICE, array($this->translator), $contactService);
        	$formArray[$contactService->getId()] = $form->createView();
    	}

    	$form = $this->formFactory->create(FormFactory::TYPE_CONTACT_SERVICE, array($this->translator, null));
    	$formArray["-1"] = $form->createView();

    	return $this->render(
            'ClarolineCoreBundle:Contact:editServices.html.twig',
            array(
               'forms' => $formArray,
               'contacts' => $contactsCollection,
            )
        );
    }

    /**
     * @Route("/contact/services/save/{id}", name="contact_save_services")
     *
     * @SEC\PreAuthorize("hasRole('ADMIN')")
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function saveServicesAction(User $loggedUser, $id  ) {

        if(!$loggedUser->hasRole('ROLE_ADMIN')){
            throw new AccessDeniedException("Access denied");
        }

        $contactService = null;
        if($id !== -1){
	        $em = $this->getDoctrine()->getManager();
	        $contactService = $em->getRepository('ClarolineCoreBundle:Contact\Contact')->find($id);
	    } 

	    $form = $this->formFactory->create(FormFactory::TYPE_CONTACT_SERVICE, array($this->translator), $contactService);
	    $form->handleRequest($this->request);


        if ($form->isValid()) {
            $contactService = $form->getData();
            
            $this->contactManager->updateContact($contactService);
        } 

    	return $this->redirect($this->generateUrl('contact_edit_services'));
    }

    /**
     * @Route("/contact/services/delete/{id}", name="contact_delete_services")
     *
     * @SEC\PreAuthorize("hasRole('ADMIN')")
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function deleteServicesAction(User $loggedUser, $id  ) {

        if(!$loggedUser->hasRole('ROLE_ADMIN')){
            throw new AccessDeniedException("Access denied");
        }

        $em = $this->getDoctrine()->getManager();
        $contactService = $em->getRepository('ClarolineCoreBundle:Contact\Contact')->find($id);


        if($contactService !== null){
            $this->contactManager->deleteContact($contactService);
        }

    	return $this->redirect($this->generateUrl('contact_edit_services'));
    }


}