<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;


/*
 * Reusable code in indexable classes 
 */

trait IndexableResourceNodeTrait
{
    public function fillIndexableDocument(&$doc)
    {
        parent::fillIndexableDocument($doc); 
        
        $doc->resource_id = $this->getResourceNode()->getId();
        $doc->resource_url = $this->get('router')->generate('claro_resource_open', array(
            'resourceType' => $this->getResourceNode()->getResourceType()->getName(),
            'node' => $this->getResourceNode()->getId()
        ));
        $doc->resource_name = $this->getResourceNode()->getName();
        $doc->wks_id = $this->getResourceNode()->getWorkspace()->getId();
        $doc->creation_date = $this->getResourceNode()->getCreationDate();
        $doc->modification_date = $this->getResourceNode()->getModificationDate();

        $doc->owner_id = $this->getResourceNode()->getCreator()->getId();
        $doc->owner_name = $this->getResourceNode()->getCreator()->getFirstName() . ' ' .
                           $this->getResourceNode()->getCreator()->getLastName();
        $doc->owner_profil_url = $this->get('router')->generate('claro_public_profile_view', array(
            'publicUrl' => $this->getResourceNode()->getCreator()->getPublicUrl()
        ));
        return $doc;
    }
    
    public function getAccessRoleIds()
    {   
        $roleManager = $this->get('claroline.manager.role_manager');
        $workspace = $this->getResourceNode()->getWorkspace();
        $workspaceConfigurableRoles = $roleManager->getWorkspaceConfigurableRoles($workspace);
        
        $rolesList = array();
        $rolesList [] = $roleManager->getRoleByName('ROLE_ADMIN')->getId();
        $rolesList [] = $roleManager->getManagerRole($workspace)->getId();
        
        foreach ($workspaceConfigurableRoles as $role) {
             $resourceRights = $this->get('claroline.manager.rights_manager')
                                    ->getOneByRoleAndResource(
                                            $role,
                                            $this->getResourceNode());
             
             if ( $resourceRights->getMask() & MaskDecoder::OPEN) {
                 $rolesList [] = $role->getId();
             }
        }
        
        return $rolesList;
    }
}
