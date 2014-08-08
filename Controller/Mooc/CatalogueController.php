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

/**
 * Description of StaticController
 *
 * @author Kevin Danezis <kdanezis@sii.fr>
 * 
 * @copyright 2014 @ sii.fr for Orange
 *           
 */
class CatalogueController extends Controller
{
    
    private $translator;
    private $security;
    private $router;
    
    
    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router"),
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct( SecurityContextInterface $security, UrlGeneratorInterface $router, TranslatorInterface $translator ) {
        $this->translator = $translator;
        $this->security = $security;
        $this->router = $router;
    }
    
    /**
     * @Route("/mes_moocs", name="solerni_mes_moocs")
     *
     * @ParamConverter("user", options={"authenticatedUser" = true })
     */    
    public function pageMesMoocs( $user ){
        return $this->render(
            'ClarolineCoreBundle:Mooc:mesMoocs.html.twig',
            array()
        );

    }
        
    /**
     * @Route("/catalogue", name="solerni_catalogue")
     * 
     * @ParamConverter("user", options={"authenticatedUser" = false })
     */
    public function pageCatalogueAction( $user )
    {

        return $this->render(
            'ClarolineCoreBundle:Mooc:catalogue.html.twig',
            array()
        );
    }
    
    /**
     * @Route("/entreprise/catalogue/{ownerName}/{ownerId}", name="solerni_owner_catalogue")
     * 
     * @ParamConverter("user", options={"authenticatedUser" = false })
     * @ParamConverter("owner", class="ClarolineCoreBundle:Mooc\MoocOwner", options={"id" = "ownerId"})
     */
    public function moocOwnerCatalogueAction( $user, $owner, $ownerName )
    {
     
        // We need to check if the user have the right to see each session
        $sessionsByUsersRepository = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');
        $autorizedSessions = array();
        
        foreach(  $owner->getMoocs() as $mooc ) {
            foreach( $mooc->getMoocSessions() as $session ) {
                // Mooc is public, OR user is member of the session OR user is admin = add session to the list
                if (
                    $mooc->getIsPublic() ||
                    $user instanceof \Claroline\CoreBundle\Entity\User &&
                    $user->hasRole('ROLE_ADMIN') || 
                    $session->getUsers()->contains( $user ) ||
                    $sessionsByUsersRepository->findOneBy( array( 'moocSession' => $session, 'user' => $user) ) ) 
                {
                    $autorizedSessions[] = $session;
                }
            }
        }
        
        return $this->render(
            'ClarolineCoreBundle:Mooc:ownerCatalogue.html.twig',
            array(
                'owner'     => $owner,
                'user'      => $user,
                'sessions'  => $autorizedSessions
            )
        );
    }


}