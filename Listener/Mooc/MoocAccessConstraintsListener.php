<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Claroline\CoreBundle\Listener\Mooc;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;

class MoocAccessConstraintsListener extends ContainerAware
{
   
    public function postUpdate( LifecycleEventArgs $args ) {
        
        $entity = $args->getEntity();
        //$entityManager = $args->getEntityManager();
        
        if ( $entity instanceof MoocAccessConstraints ) {
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgrade(array($entity));
        }
        if ( $entity instanceof User ) {
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgrade(array(), $entity);
        }
        if ( $entity instanceof MoocSession ) {
        	$constraints = array();
        	$accessContraints = $entity->getMooc()->getAccessConstraints();
    		if(!empty($accessContraints)) {
    			$constraints = $constraints;
    		} 
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgrade($constraints);
        }
        if ( $entity instanceof Mooc ) {
        	$constraints = array();
        	$accessContraints = $entity->getAccessConstraints();
    		if(!empty($accessContraints)) {
    			$constraints = $constraints;
    		} 
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgrade($constraints);
        }
    }

    public function postPersist( LifecycleEventArgs $args ) {
        
        $entity = $args->getEntity();
        //$entityManager = $args->getEntityManager();
        
        if ( $entity instanceof MoocAccessConstraints ) {
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgrade(array($entity));
        } 
        if ( $entity instanceof User ) {
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgrade(array(), $entity);
        }  
        if ( $entity instanceof MoocSession ) {
        	$constraints = array();
        	$accessContraints = $entity->getMooc()->getAccessConstraints();
    		if(!empty($accessContraints)) {
    			$constraints = $constraints;
    		} 
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgrade($constraints);
        }
        if ( $entity instanceof Mooc ) {
        	$constraints = array();
        	$accessContraints = $entity->getAccessConstraints();
    		if(!empty($accessContraints)) {
    			$constraints = $constraints;
    		} 
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgrade($constraints);
        }
        
    }

    public function postRemove( LifecycleEventArgs $args ) {
        
        $entity = $args->getEntity();
        //$entityManager = $args->getEntityManager();
        
        if ( $entity instanceof MoocAccessConstraints ) {
        	//$service = $this->container->get('orange.moocaccesscontraints_service');
            //$service->processDelete(array($entity));
        }
        
        
    }
}
