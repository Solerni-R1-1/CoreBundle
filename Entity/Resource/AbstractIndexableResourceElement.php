<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\IndexableInterface;
use Claroline\CoreBundle\Entity\IndexableTrait;
use Claroline\CoreBundle\Entity\Resource\IndexableResourceNodeTrait;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\AbstractIndexable;

/**
 * Extend from this class to make your resource indexable 
 *
 */

abstract class AbstractIndexableResourceElement extends AbstractIndexable
{
   
    use IndexableTrait;
    use IndexableResourceNodeTrait;
    
    abstract public function getResourceNode();
}
