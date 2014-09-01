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


    public function buildResultFacet($resultFacet) 
    {
        
        $returnResultFacet  = $this->getResultFacet();
        
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
                       'label' => $moocCategory->getName()
                );
            }
        }
        return $returnResultFacet;
    }

}
