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
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

class MoocAccessConstraintsListener extends ContainerAware
{
	public function __construct() {
		
	}

    public function postUpdate(LifecycleEventArgs $args) {
        $this->postPersist($args);
    }

    public function postPersist(LifecycleEventArgs $args) {

        $entity = $args->getEntity();

        if ($entity instanceof MoocAccessConstraints) {
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processUpgradeConstraints(array($entity));
        } elseif ($entity instanceof User) {
            $uow = $this->container->get("doctrine.orm.entity_manager")->getUnitOfWork();
            $changeSet = $uow->getEntityChangeSet($entity);
            // Only check Constraint if the mail changes
            if (array_key_exists("mail", $changeSet)) {
                $service = $this->container->get('orange.moocaccesscontraints_service');
                $service->processUpgradeUsers(array($entity));
            }
        }

        // Code moved to WorkspaceManager.createWorkspace() postUpdateListener.
        // http://docs.doctrine-project.org/en/2.0.x/reference/events.html#postupdate-postremove-postpersist
        // can't modify entity or entity relations in postUpdate of this entity...
    }

    public function preRemove(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();

        if ($entity instanceof MoocAccessConstraints) {
            $service = $this->container->get('orange.moocaccesscontraints_service');
            $service->processDelete(array($entity));
        }
    }

}
