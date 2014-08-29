<?php

namespace Claroline\CoreBundle\SearchFilter\Mooc;

use Orange\SearchBundle\SearchFilter\AbstractFilter;

/**
 * Description of FilterType
 *
 * @author aameziane
 */
class FilterOwner extends AbstractFilter
{

    public static function getName() {
        return 'mooc_owner_name';
    }
    
    
    public static function getShortCut() {
        return 'owner';
    }
    
    
    public static function getViewType() {
        return 'checkbox-all';
    }
    
}
