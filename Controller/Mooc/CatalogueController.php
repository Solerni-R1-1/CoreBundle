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
            'ClarolineCoreBundle:Tool\desktop\moocs:desktopMoocsLayout.html.twig',
            array(
               // 'mooc'      => $mooc,
                //'sessions'  => $sessions,
                //'user'      => $user
            )
        );

    }
        
    /**
     * @Route("/catalogue", name="solerni_catalogue")
     * 
     * @ParamConverter("user", options={"authenticatedUser" = false })
     */
    public function pageCatalogueAction( $user ){

        $ws_url = 'https://mooc-dev.rennes.sii.fr/mooc/search/query.json';


        //Parameters for the POST request
        $parameters = <<<JSON
        {
            "page":1,
            "item_per_page":2,
            "keywords":"", 
            "selections": {
                "type_name":{
                    "claroline_core_mooc_moocsession":true
                }
            }
        }
JSON;
        $postdata = http_build_query(json_decode($parameters, true));

        // Create a stream
        $opts = stream_context_create(array(
          'http'=>array(
            'method'=>'POST',
            'header'=>"Content-type: application/json\r\n".
                          "content: $postdata"
          )
        ));

        $json = @file_get_contents($ws_url, false, $context);
        if($json == null){
            echo ("WS url not found, check your configuration : <br/>{$ws_url}<br/> -->\n");
            die();
            return;
        }
        //$json = @json_decode($json)->documents;
        //echo $json;
        //die();
         //$response = $this->forward('orange_search_request', array(
             /*   'name'  => $name,
                'color' => 'green',*/
         //   ));

        return $this->render(
            'ClarolineCoreBundle:Mooc:catalogue.html.twig',
            array(
               // 'mooc'      => $mooc,
                //'sessions'  => $sessions,
                //'user'      => $user
            )
        );
    }


}