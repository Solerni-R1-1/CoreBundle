<?php


namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Repository\Mooc\MoocRepository;
use Claroline\CoreBundle\Repository\Mooc\MoocSessionRepository;
use Icap\LessonBundle\Entity\Lesson;
use Claroline\CoreBundle\Controller\SolerniController;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Entity\User;

/**
 * Description of StaticController
 *
 * @author Kevin Danezis <kdanezis@sii.fr>
 * 
 * @copyright 2014 @ sii.fr for Orange
 *           
 */
class AdministrationController extends Controller
{

  /**
     * Lists mooc administration options.
     *
     * @Route("/", name="admin_parameters_mooc")
     * @Method("GET")
     * @Template()
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function indexAction( User $user )
    {
        $moocs = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Mooc\Mooc')->findAll();

        return array(
            'moocs' => $moocs
        );
    }

}