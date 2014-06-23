<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Entity\IndexableInterface;
use Claroline\CoreBundle\Entity\IndexableTrait;

/**
 * extend this class to make your entity indexable
 *
 */

abstract class AbstractIndexable implements IndexableInterface
{

    use IndexableTrait;

    public function fillIndexableDocument(&$doc)
    {
        $doc->id = $this->getIndexableDocId();
        $doc->entity_id = $this->getId();
        $doc->type_name = $this->getTypeName();
        return $doc;
    }

    
    public function fillIndexableDocResourceNodeInfo(&$doc, $resourceNode)
    {
        $doc->resource_id = $resourceNode->getId();
        $doc->resource_url = $this->get('router')->generate('claro_resource_open', array(
            'resourceType' => $resourceNode->getResourceType()->getName(),
            'node' => $resourceNode->getId()
        ));
        $doc->wks_id = $resourceNode->getWorkspace()->getId();
    }

}
