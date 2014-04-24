<?php


namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Description of StaticController
 *
 * @author Kevin Danezis <kdanezis@sii.fr>
 * @author Anas AMEZIANE <aameziane@sii.fr>
 * 
 * @copyright 2014 @ sii.fr for Orange
 *           
 */
class StaticController extends Controller
{
        /**
         * @Route("/page/{name}", name="solerni_static_page")
         */
        public function getStaticPageAction($name){
            
            $redirectUrl = $this->getStaticPage( $name );
            if ( $redirectUrl === null ) {
                return $this->redirect( $this->generateUrl( 'solerni_static_page_error_type_parameters', array( 'name' => $name ) ) );
            } else {
                return $this->redirect( $redirectUrl );
            }
        }
        
        public function getStaticPage($name){
        
            // check values into parameters.yml. Also take a look inside README.md for example
            // it's must be something like "solerni_static_$name"
            
            if($this->container->hasParameter('solerni_' . $name)) {
               return $this->container->getParameter('solerni_' . $name);
            } else {
               return null;
            }
        }
        
        /**
         * @Route("error/missing-parameter/{name}", name="solerni_static_page_error_type_parameters")
         */
        public function RedirectErrorpageAction( $name ) {
             return $this->render(
            'OrangeThemeBundle:Errors:parametersMissing.html.twig',
             array ( 'name' => $name )
        );
            
        }

}