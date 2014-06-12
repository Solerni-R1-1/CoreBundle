<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class SolerniExtension extends \Twig_Extension
{
    private $container;
    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct( $container ) 
    {        
        $this->container = $container;
    }
    
    
    public function getFunctions()
    {
        return array(
            'isCurrentPage' => new \Twig_Function_Method( $this, 'solerniCompareRoute' ),
            'isCurrentLoginPage' => new \Twig_Function_Method( $this, 'solerniCompareLoginRoute' ),
            'checkChapterLevel' => new \Twig_Function_Method( $this, 'solerniCheckChapterLevel' )
        );
    }
    
    public function getFilters()
    {
        return array(
            'widgetForumDate' => new \Twig_Filter_Method( $this, 'solerniWidgetForumDate' ),
            'textTruncate' => new \Twig_Filter_Method( $this, 'TwigTruncateFilter' )
        );
    }
    /**
     * Twig function
     * Compare tested route with current URL
     * @return bolean 
     */
    public function solerniCompareRoute( $currentItemRoute )
    {
        $router = $this->container->get('router');
        
        // REMOVE LAST PART OF URL
        // MEANING ROUTE MINUS {resourceId}
        $currentUrl = dirname( $_SERVER['REQUEST_URI'] );
         
        if ( strpos(  $currentItemRoute, $currentUrl ) === false ) {
            return false;
        } else {
            return true;
        }
    }
    /*
     * 
     */
    public function solerniCompareLoginRoute( $currentItemRoute )
    {
        if ( strpos( $_SERVER['REQUEST_URI'], $currentItemRoute ) === false )
        {
            return false;
        } else {
            return true;
        }
    }
    
    /*
     * Check current chapter level for provided slug
     * 
     * @param array $tree
     * @param string $slug
     * 
     * @return int
     */
    public function solerniCheckChapterLevel( $tree, $slug )
    {        

        if ( ! $slug  )
        {
            return -1;
        }

        if ( $slug == $tree['slug'])
        {
                return $tree['level'];
        }
 
        if ( count ( $tree['__children'] ) > 0 )
        { 

                foreach ( $tree['__children'] as $children )
                {
                    $return = $this->solerniCheckChapterLevel( $children, $slug );
                    if( $return >= 0 ) 
                    {
                        return $return;
                    }
                }
        }
        return -1;
    }
    
    /*
     * Transform date object to string
     * 
     * @param ObjectDate | String $date 
     * @return string
     */
    public function solerniWidgetForumDate( $date, $locale="fr" )
    {
        if ( $locale == "fr" ) {
            setlocale( LC_TIME, 'fr_FR.utf8','fra' ); 
        }
        
        if (is_string($date)) {
            $messageTimestamp = strtotime($date);
         } else {
            $messageTimestamp = $date->getTimestamp();
        }
        
        $date = date( 'd F Y', $messageTimestamp );
        $today = date( 'd F Y', time() );
        $tomorrow = date( 'd F Y', time() - ( 24 * 60 * 60 ) );

        if ($date == $today) {
          $return = "aujourd'hui";
        } else if ($date == $tomorrow) {
          $return = "hier";
        } else {
            $return = strftime( "%A %d %B ", $messageTimestamp );
        }
        $return .= ' à ';

        $return .= date( 'G', $messageTimestamp ) . 'h' . date( 'i', $messageTimestamp );
        return $return;
    }

    /*
    * Truncate text
    * @param string $text Text to trim.
    * @param int $num_words Number of words. Default 55.
    * @param string $more Optional. What to append if $text needs to be trimmed. Default '&hellip;'.
    * @return string Trimmed text.
    */
    function TwigTruncateFilter( $text, $num_words = 55, $more = null ) {
        if ( null === $more )
            $more = '&hellip;';
        $original_text = $text;
        $text = strip_tags( $text );
        $words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
        $sep = ' ';
        
        if ( count( $words_array ) > $num_words ) {
            array_pop( $words_array );
            $text = implode( $sep, $words_array );
            $text = $text . $more;
        } else {
            $text = implode( $sep, $words_array );
        }
       return $text;
    }
    
    public function getName()
    {
        return 'solerni_twig_extension';
    }
}