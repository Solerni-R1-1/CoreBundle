<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Claroline\CoreBundle\Listener\Mooc;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;

class MoocAccessConstraintsListener
{
   
    public function postUpdate( LifecycleEventArgs $args ) {
        
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        
        if ( $entity instanceof MoocAccessConstraints ) {
            $userRepository = $entityManager->getRepository('ClarolineCoreBundle:User');
        }
        
        
    }
}
