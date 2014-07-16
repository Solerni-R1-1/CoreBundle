<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Claroline\CoreBundle\Listener\Mooc;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;
use Symfony\Component\DependencyInjection\ContainerAware;

class MoocAccessConstraintsListener extends ContainerAware
{
   
    public function postUpdate( LifecycleEventArgs $args ) {
        
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        
        if ( $entity instanceof MoocAccessConstraints ) {
            $userRepository = $entityManager->getRepository('ClarolineCoreBundle:User');
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->process(array($entity));
        }
        //$this->container->get('logger')->error("test1.2");
        //die("end MoocAccessConstraintsListener->postUpdate()");
    }

    public function postPersist( LifecycleEventArgs $args ) {
        
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        
        if ( $entity instanceof MoocAccessConstraints ) {
            $userRepository = $entityManager->getRepository('ClarolineCoreBundle:User');
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->process(array($entity));
        	//$this->container->get('logger')->error("test2.1");
        	    //die('ok');
        }
        //$this->container->get('logger')->error("test2.2");
        //echo get_class($entity).'<hr/>' ;
        //die("end MoocAccessConstraintsListener->postPersist() ". get_class($entity));
        
        
    }

    public function postRemove( LifecycleEventArgs $args ) {
        
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        
        if ( $entity instanceof MoocAccessConstraints ) {
            $userRepository = $entityManager->getRepository('ClarolineCoreBundle:User');
        }
        
        
    }
}
