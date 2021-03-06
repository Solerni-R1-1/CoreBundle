<?php

namespace Claroline\CoreBundle\SearchFilter\Mooc;

use Orange\SearchBundle\Filter\FilterStandard;

/**
 * Description of FilterDuration
 *
 * @author aameziane
 */
class FilterDuration extends FilterStandard
{
    

    public function getQueryExpression($values)
    {
        
        $expression = array();
        foreach ($values as $key) {
            switch ($key) {
                case 'less_4':
                    $expression [] = 'mooc_duration_i:[* TO 3]';
                    break;
                case 'between_4_6':
                    $expression [] = 'mooc_duration_i:[4 TO 6]';
                    break;
                case 'more_6':
                    $expression [] = 'mooc_duration_i:[7 TO *]';
                    break;
                default:
                    break;
            }
        }
        return "(" . implode(" OR ", $expression) . ")";
    }
    
    public function createFacet(&$facetSet)
    {
        $facetSet->createFacetMultiQuery($this->getShortCut())
                             ->createQuery('less_4', 'mooc_duration_i:[* TO 3]')
                             ->createQuery('between_4_6', 'mooc_duration_i:[4 TO 6]')
                             ->createQuery('more_6', 'mooc_duration_i:[7 TO *]');
    }
    
    public function postProcessResultFacet($resultFacet) 
    {
        $returnResultFacet  = $this->initResultFacet();
        
        foreach ($resultFacet as $value => $count) {
            $returnResultFacet ['value'] [] = array(
                'count' => $count,
                'value' => $value,
                'label' => $this->get('translator')->trans($value, array(), 'search')
            );
        }
        return $returnResultFacet;
    }
}
