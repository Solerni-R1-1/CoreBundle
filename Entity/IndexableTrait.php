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

/*
 * Reusable code in indexable classes 
 */

trait IndexableTrait
{

    public function getIndexableDocId()
    {
        return base64_encode(get_class($this) . ':' . $this->getId());
    }

    public function getTypeName()
    {
        //generate type_name ex : claroline_forum_message
        $class_name = strtolower(str_replace("Bundle", "", get_class($this)));
        $class_name_array = explode("\\", $class_name);
        if (($key = array_search('entity', $class_name_array)) !== false) {
            unset($class_name_array[$key]);
        }
        return implode("_", $class_name_array);
    }

}
