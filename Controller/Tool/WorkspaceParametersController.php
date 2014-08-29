<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Form\Mooc\MoocType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormError;

class WorkspaceParametersController extends Controller
{
    private $workspaceManager;
    private $workspaceTagManager;
    private $security;
    private $eventDispatcher;
    private $formFactory;
    private $router;
    private $request;
    private $localeManager;
    private $userManager;
    private $tosManager;
    private $utilities;

    /**
     * @DI\InjectParams({
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "security"            = @DI\Inject("security.context"),
     *     "eventDispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"         = @DI\Inject("claroline.form.factory"),
     *     "router"              = @DI\Inject("router"),
     *     "localeManager"       = @DI\Inject("claroline.common.locale_manager"),
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "tosManager"          = @DI\Inject("claroline.common.terms_of_service_manager"),
     *      "utilities"          = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        SecurityContextInterface $security,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        UrlGeneratorInterface $router,
        Request $request,
        LocaleManager $localeManager,
        UserManager $userManager,
        TermsOfServiceManager $tosManager,
        ClaroUtilities $utilities
    )
    {
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->request = $request;
        $this->localeManager = $localeManager;
        $this->userManager = $userManager;
        $this->tosManager = $tosManager;
        $this->utilities = $utilities;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/form/export",
     *     name="claro_workspace_export_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceExportFormAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/export",
     *     name="claro_workspace_export"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function workspaceExportAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('name')->getData();
            $this->workspaceManager->export($workspace, $name);
            $route = $this->router->generate(
                'claro_workspace_open_tool',
                array('toolName' => 'parameters', 'workspaceId' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/editform",
     *     name="claro_workspace_edit_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceEditFormAction(AbstractWorkspace $workspace)
    {
        $user = $this->security->getToken()->getUser();
        $this->checkAccess($workspace);
        $username = is_null( $workspace->getCreator()) ? '' : $workspace->getCreator()->getUsername(); 
        $creationDate = is_null(
                            $workspace->getCreationDate()) ? 
                            null : $this->utilities->intlDateFormat($workspace->getCreationDate());
        $count = $this->workspaceManager->countUsers($workspace->getId());
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_EDIT, array($username, $creationDate, $count), $workspace);

        
        if ($workspace->getSelfRegistration()) {
            $url = $this->router->generate(
                'claro_workspace_subscription_url_generate',
                array('workspace' => $workspace->getId()),
                true
            );
        } else {
            $url = '';
        }
        
        /* Add custom form Mooc if workspace is mooc */
        if ( $workspace->isMooc() ) {
            
            /* Get lessons and forums from current workspace*/
            $mooc = $workspace->getMooc();
            $doctrine = $this->getDoctrine();

            $resourceTypeRepository = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceType');
            $forumResourceType = $resourceTypeRepository->findOneByName('claroline_forum');
            $lessonResourceType = $resourceTypeRepository->findOneByName('icap_lesson');
            
            /* generate form */
            $form_mooc = $this->formFactory->create( FormFactory::TYPE_MOOC, array( $workspace, $lessonResourceType, $forumResourceType ), $mooc );
            
            /* Return MOOC data and form */
            $returnArray = array(
                'form' => $form->createView(),
                'form_mooc' => $form_mooc->createView(),
                'workspace' => $workspace,
                'url' => $url,
                'user' => $user,
                'count' => $count,
                'illustration' => $mooc->getIllustrationWebPath()
            ); 
        } else {
            $form->add('isMooc', 'checkbox', array( 'required' => false, 'data' => false ));
            /* return only WS data if not MOOC */
            $returnArray = array(
                'form' => $form->createView(),
                'workspace' => $workspace,
                'url' => $url,
                'user' => $user,
                'count' => $count
            ); 
        }
        
        return $returnArray;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/edit",
     *     name="claro_workspace_edit"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceEditAction(AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $wsRegisteredName = $workspace->getName();
        $wsRegisteredCode = $workspace->getCode();
        $wsRegisteredDisplayable = $workspace->isDisplayable();
        $isMooc = $workspace->isMooc();   
        $user = $this->security->getToken()->getUser();
        $count = $this->workspaceManager->countUsers($workspace->getId());
        if ($workspace->getSelfRegistration()) {
            $url = $this->router->generate(
                'claro_workspace_subscription_url_generate',
                array('workspace' => $workspace->getId()),
                true
            );
        } else {
            $url = '';
        }
        
        // workspace form + possible checkbox isMooc
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_EDIT, array(), $workspace);
        $form->add('isMooc', 'checkbox', array( 'required' => false, 'data' => false ));
        $form->handleRequest($this->request);
        
        // Creating the mooc is we checkboxed isMooc
        if ( $form->get('isMooc')->getData() && ! $isMooc ) {
            $isMooc = $workspace->setIsMooc( $form->get('description')->getData() );
        }
        
        if ( $isMooc ) { // 
            $mooc = $workspace->getMooc();
            /* Store current sessions to compare with submitted form */
            $originalSessions = new ArrayCollection();
            foreach ( $mooc->getMoocSessions() as $session ) {
                $originalSessions->add( $session );
            }

            /* Store current contraints to compare with submitted form */
            $originalConstraints = new ArrayCollection();
            foreach ( $mooc->getAccessConstraints() as $constraint ) {
                $originalConstraints->add( $constraint );
            }
        
            /* Get lessons and forums from current workspace*/
            $doctrine = $this->getDoctrine();
            $resourceTypeRepository = $doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceType');
            $forumResourceType = $resourceTypeRepository->findOneByName('claroline_forum');
            $lessonResourceType = $resourceTypeRepository->findOneByName('icap_lesson');
            
            // Generate mooc form
            $form_mooc = $this->formFactory->create(FormFactory::TYPE_MOOC, array( $workspace, $lessonResourceType, $forumResourceType ), $mooc );
            $form_mooc->handleRequest($this->request);
        }
        
        if ( $isMooc ) {
            if ( $form->isValid() || && $form_mooc->isValid() ) {
            	$forumIds = array();
            	$hasErrors = false;
                /* Setting current mooc for newly added sessions */
                foreach ( $mooc->getMoocSessions() as $i => $moocSession ) {
                   	if (  ! $moocSession->getMooc() ) {
                        $moocSession->setMooc( $mooc );
                   	}
					
                   	$forum = $moocSession->getForum();
                   	$form_session = $form_mooc->get('moocSessions')->get($i);
                   	if ($forum != null) {
	                   	if (in_array($forum->getId(), $forumIds)) {
		                   	$form_session->get('forum')->addError(new FormError($this->get('translator')->trans(
						            'error_sessions_same_forum', 
						            array(), 
						            'platform'
					            )));
		                   	$hasErrors = true;
	                   	}
						$forumIds[] = $forum->getId();
	                }
	                
	                $startDate = $moocSession->getStartDate();
	                $endDate = $moocSession->getEndDate();
	                $startInscriptionDate = $moocSession->getStartInscriptionDate();
	                $endInscriptionDate = $moocSession->getEndInscriptionDate();
	                
	                if ($endDate <= $startDate) {
	                	$form_session->get('endDate')->addError(new FormError($this->get('translator')->trans(
						            'error_session_end_date_inferior_start_date', 
						            array(), 
						            'platform'
					            )));
		                $hasErrors = true;
	                }
	                if ($startInscriptionDate >= $startDate) {
	                	$form_session->get('startInscriptionDate')->addError(new FormError($this->get('translator')->trans(
						            'error_session_invalid_start_inscription_date', 
						            array(), 
						            'platform'
					            )));
		                $hasErrors = true;
	                	
	                }
	                if ($endInscriptionDate <= $startInscriptionDate) {
	                	$form_session->get('endInscriptionDate')->addError(new FormError($this->get('translator')->trans(
						            'error_session_end_inscription_date_inferior_start_inscription_date', 
						            array(), 
						            'platform'
					            )));
		                $hasErrors = true;
	                	
	                }
	                if ($endInscriptionDate > $endDate) {
	                	$form_session->get('endInscriptionDate')->addError(new FormError($this->get('translator')->trans(
						            'error_session_end_inscription_date_superior_end_date', 
						            array(), 
						            'platform'
					            )));
		                $hasErrors = true;
	                	
	                }
                }
                
                if ($hasErrors) {
                	return array(
                			'form' => $form->createView(),
                			'form_mooc' => $form_mooc->createView(),
                			'workspace' => $workspace,
                			'url' => $url,
                			'user' => $user,
                			'count' => $count,
                			'illustration' => ($mooc != null ? $mooc->getIllustrationWebPath() : '')
                	);
                }

                /* remove sessions from database if deleted */
                foreach ( $originalSessions as $moocSession ) {
                    if ( $mooc->getMoocSessions()->contains($moocSession) == false ) {
                        $this->getDoctrine()->getManager()->remove($moocSession);
                    }
                }

               /* Setting current mooc for each constraint */
                foreach ( $mooc->getAccessConstraints() as $accessConstraint ) {              
                    $accessConstraint->addMooc($mooc);
                }


                /*Remove mooc from constraint deleted from mooc*/
                foreach ( $originalConstraints as $constraint) {
                    if ( $mooc->getAccessConstraints()->contains($constraint) == false ) {
                        $constraint->removeMooc($mooc);
                    }
                }

                $this->workspaceManager->createWorkspace($workspace);
                $this->workspaceManager->rename($workspace, $workspace->getName());
                $displayable = $workspace->isDisplayable();

                if (!$displayable && $displayable !== $wsRegisteredDisplayable) {
                    $this->workspaceTagManager->deleteAllAdminRelationsFromWorkspace($workspace);
                }

                return $this->redirect(
                    $this->generateUrl( 'admin_parameters_mooc')
                );
            } else {
                $workspace->setName($wsRegisteredName);
                $workspace->setCode($wsRegisteredCode);
            }


            return array(
                'form' => $form->createView(),
                'form_mooc' => $form_mooc->createView(),
                'workspace' => $workspace,
                'url' => $url,
                'user' => $user,
                'count' => $count,
                'illustration' => ($mooc != null ? $mooc->getIllustrationWebPath() : '')
            );
        } else {
            if ( $form->isValid() ) {
                $this->workspaceManager->createWorkspace($workspace);
                $this->workspaceManager->rename($workspace, $workspace->getName());
                $displayable = $workspace->isDisplayable();

                if (!$displayable && $displayable !== $wsRegisteredDisplayable) {
                    $this->workspaceTagManager->deleteAllAdminRelationsFromWorkspace($workspace);
                }

                return $this->redirect(
                    $this->generateUrl(
                        'claro_workspace_open_tool',
                        array(
                            'workspaceId' => $workspace->getId(),
                            'toolName' => 'parameters'
                        )
                    )
                );
            } else {
                $workspace->setName($wsRegisteredName);
                $workspace->setCode($wsRegisteredCode);
            }
        }
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tool/{tool}/config",
     *     name="claro_workspace_tool_config"
     * )
     *
     * @param AbstractWorkspace $workspace
     * @param Tool              $tool
     *
     * @return Response
     */
    public function openWorkspaceToolConfig(AbstractWorkspace $workspace, Tool $tool)
    {
        $this->checkAccess($workspace);
        $event = $this->eventDispatcher->dispatch(
            strtolower('configure_workspace_tool_' . $tool->getName()),
            'ConfigureWorkspaceTool',
            array($tool, $workspace)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate",
     *     name="claro_workspace_subscription_url_generate"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:generate_url_subscription.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function urlSubscriptionGenerateAction(AbstractWorkspace $workspace)
    {
        $user = $this->security->getToken()->getUser();

        if ( $user === 'anon.') {
            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_subscription_url_generate_anonymous',
                    array(
                        'workspace' => $workspace->getId(),
                        'toolName' => 'home'
                    )
                )
            );
        }

        $this->workspaceManager->addUserAction($workspace, $user);

        return $this->redirect(
            $this->generateUrl('claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'home'))
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate/anonymous",
     *     name="claro_workspace_subscription_url_generate_anonymous"
     * )
     * @EXT\Method({"GET","POST"})
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:generate_url_subscription_anonymous.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     */
    public function anonymousSubscriptionAction(AbstractWorkspace $workspace)
    {
        if (!$workspace->getSelfRegistration()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->formFactory->create(
            FormFactory::TYPE_USER_BASE_PROFILE, array($this->localeManager, $this->tosManager)
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $user->setFacebookAccount(false); 
            $this->userManager->createUser($user);
            $this->workspaceManager->addUserAction($workspace, $user);
            return $this->redirect(
                $this->generateUrl('claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'home')));
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    private function checkAccess(AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
    
}
