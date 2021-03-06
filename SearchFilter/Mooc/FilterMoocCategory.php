<?php

namespace Claroline\CoreBundle\SearchFilter\Mooc;

use Orange\SearchBundle\Filter\FilterStandard;

/**
 * Description of FilterMoocCategory
 *
 * @author aameziane
 */
class FilterMoocCategory extends FilterStandard
{


    public function postProcessResultFacet($resultFacet) 
    {
        
        $returnResultFacet  = $this->initResultFacet();
        
        foreach ($resultFacet as $value => $count) {
        /* @var $moocSession \Claroline\CoreBundle\Entity\Mooc\MoocCategory */
            $moocCategory = $this->get('doctrine')
                ->getEntityManager()
                ->getRepository("ClarolineCoreBundle:Mooc\MoocCategory")
                ->findOneById($value);

            if ($moocCategory) {
                $returnResultFacet ['value'] []= array(
                       'count' => $count, 
                       'value' => $value,
                       'label' => $this->get('translator')->trans( $moocCategory->getName(), array(), 'platform' )
                );
            }
        }
        return $returnResultFacet;
    }

}
