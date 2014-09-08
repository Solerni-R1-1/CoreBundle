<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\IndexableInterface;
use Claroline\CoreBundle\Entity\IndexableTrait;
use Claroline\CoreBundle\Entity\Resource\IndexableResourceNodeTrait;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Extend from this class to make your resource indexable 
 *
 */

abstract class AbstractIndexableResource extends AbstractResource
{
   
    use IndexableResourceNodeTrait;
}
