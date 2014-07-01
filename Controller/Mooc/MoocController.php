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
use Claroline\CoreBundle\Repository\MoocRepository;
use Claroline\CoreBundle\Repository\MoocSessionRepository;

/**
 * Description of StaticController
 *
 * @author Kevin Danezis <kdanezis@sii.fr>
 * 
 * @copyright 2014 @ sii.fr for Orange
 *           
 */
class MoocController extends Controller
{
	private $patternId = '/^[0-9]+$/';
	private $patternName = '/^[0-9a-zA-Z\-\_](+)$/';

        /**
         * @Route("/{moocid}/{moocname}/sessions", name="mooc_view")
         */
        public function moocPageAction($moocid, $moocname){
            //Check pattern
            if(!preg_match($this->patternId, $moocid)){
            	return $this->inner404("parametre moocid invalid : ".$moocid);
            }

            //check the mooc
            $mooc = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Workspace\Mooc')
            		->find($moocid);

			if($mooc == null){
            	return $this->inner404("le mooc n'existe pas : ".$moocid);
            }

            return $this->render(
                'ClarolineCoreBundle:Mooc:mooc.html.twig',
                array(
                    'mooc'     => $mooc
                )
            );
        }
        
        /**
         * @Route("/{moocid}/{moocname}/session/{sessionid}/{word}", name="mooc_view_session")
         */
        public function sessionPageAction($moocid, $moocname, $sessionid, $word){


        	if(!preg_match($this->patternId, $moocid)){
            	return $this->inner404("parametre moocid invalid : ".$moocid);
            }

            if(!preg_match($this->patternId, $sessionid)){
            	return $this->inner404("parametre sessionid invalid : ".$sessionid);
            }

            //check the mooc
            $session = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Workspace\MoocSession')->find($sessionid);
            if($session == null){
            	return $this->inner404("la session n'existe pas : ".$sessionid);
            }
            if($session->getMooc()->getId() != $moocid){
                return $this->inner404("mooc non correspondant . expected : "
                            .$session->getMooc()->getId()." given : ".$moocid);
            }


        	switch ($word){
        		case "apprendre" : 
	        		return $this->sessionApprendrePage($session);
	        		break;

        		case "discuter" : 
	        		return $this->sessionDiscuterPage($session);
	        		break;

        		case "partager" :
	        		return $this->sessionPartagerPage($session);
	        		break;

        		case "vomir" :
        		case "subir" :
	        		return $this->sessionLolPage($session);
	        		break;

        		default:return $this->inner404("le word est inconnu : ".$word);
        	}

        }

        private function inner404($warn){
            return $this->render(
                'ClarolineCoreBundle:Mooc:mooc_error.html.twig',
                array(
                    'warn'     => $warn
                )
            );
        }

        private function sessionApprendrePage($session) {
            return $this->render(
                'ClarolineCoreBundle:Mooc:redirect_tmp.html.twig',
                array(
                    'session'     => $session,
                    'redirection'     => 'Redirection vers Apprendre'
                )
            );
        }
        private function sessionDiscuterPage($session) {
            return $this->render(
                'ClarolineCoreBundle:Mooc:redirect_tmp.html.twig',
                array(
                    'session'     => $session,
                    'redirection'     => 'Redirection vers les forums'
                )
            );
        }
        private function sessionPartagerPage($session) {
            return $this->render(
                'ClarolineCoreBundle:Mooc:redirect_tmp.html.twig',
                array(
                    'session'     => $session,
                    'redirection'     => 'Redirection vers les Resources/Fichiers'
                )
            );
        }
        private function sessionLolPage($session) {
            return $this->render(
                'ClarolineCoreBundle:Mooc:redirect_tmp.html.twig',
                array(
                    'session'     => $session,
                    'redirection'     => 'Bon ben là ....'
                )
            );
        }



}