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
            'checkChapterLevel' => new \Twig_Function_Method( $this, 'solerniCheckChapterLevel' ),
            'checkChapterLevelId' => new \Twig_Function_Method( $this, 'solerniCheckChapterLevelId' ),
        );
    }
    
    public function getFilters()
    {
        return array(
            'widgetForumDate' => new \Twig_Filter_Method( $this, 'solerniWidgetForumDate' ),
            'textTruncate' => new \Twig_Filter_Method( $this, 'TwigTruncateFilter' ),
            'minsToHoursMins' => new \Twig_Filter_Method( $this, 'solerniMinsToHoursMins' ),
            'slugify' => new \Twig_Filter_Method( $this, 'solerniSlugify' ),
            'countryName' => new \Twig_SimpleFilter('countryName', array( $this, 'countryName' ))
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
        if ( is_string( $currentItemRoute ) ) {
            $currentItemRoute = str_split( $currentItemRoute, 99 );
        }
         
        foreach( $currentItemRoute as $route ) {
            if ( strpos( $_SERVER['REQUEST_URI'], $route ) !== false ) {
                return true;
            }
        }
        
        return false;
        
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
     * Check current chapter level for provided slug
     *
     * @param array $tree
     * @param string $slug
     *
     * @return int
     */
    public function solerniCheckChapterLevelId( $tree, $id )
    {

    	if ( ! $id  )
    	{
    		return -1;
    	}
    	if ( $id == $tree['id'])
    	{    		
    		return $tree['level'];
    	}
    
    	if ( count ( $tree['__children'] ) > 0 )
    	{
    
    		foreach ( $tree['__children'] as $children )
    		{
    			$return = $this->solerniCheckChapterLevelId( $children, $id );
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
    public function solerniWidgetForumDate( $date, $locale = "fr" )
    {
        
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
    
    /**
     * Convert number of minutes into hours & minutes
     * 
     * @param integer $time number of mins
     * @param string $format sprintf format
     * 
     * @return string
     */
    function solerniMinsToHoursMins($time, $format = '%d:%d')
    {
        settype($time, 'integer');
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }
    
    /**
     * Returns the slugified string.
     *
     * @param string $string    String to slugify
     * @param string $separator Separator
     *
     * @return string Slugified string
     */
    public function solerniSlugify($string, $separator = '-')
    {
        
        /** @var array */
        $rules = array(
        // Numeric characters
        '¹' => 1,
        '²' => 2,
        '³' => 3,

        // Latin
        'º' => 0,
        '°' => 0,
        'æ' => 'ae',
        'ǽ' => 'ae',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Å' => 'A',
        'Ǻ' => 'A',
        'Ă' => 'A',
        'Ǎ' => 'A',
        'Æ' => 'AE',
        'Ǽ' => 'AE',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'å' => 'a',
        'ǻ' => 'a',
        'ă' => 'a',
        'ǎ' => 'a',
        'ª' => 'a',
        '@' => 'at',
        'Ĉ' => 'C',
        'Ċ' => 'C',
        'ĉ' => 'c',
        'ċ' => 'c',
        '©' => 'c',
        'Ð' => 'Dj',
        'Đ' => 'Dj',
        'ð' => 'dj',
        'đ' => 'dj',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ĕ' => 'E',
        'Ė' => 'E',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ĕ' => 'e',
        'ė' => 'e',
        'ƒ' => 'f',
        'Ĝ' => 'G',
        'Ġ' => 'G',
        'ĝ' => 'g',
        'ġ' => 'g',
        'Ĥ' => 'H',
        'Ħ' => 'H',
        'ĥ' => 'h',
        'ħ' => 'h',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ĩ' => 'I',
        'Ĭ' => 'I',
        'Ǐ' => 'I',
        'Į' => 'I',
        'Ĳ' => 'IJ',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ĩ' => 'i',
        'ĭ' => 'i',
        'ǐ' => 'i',
        'į' => 'i',
        'ĳ' => 'ij',
        'Ĵ' => 'J',
        'ĵ' => 'j',
        'Ĺ' => 'L',
        'Ľ' => 'L',
        'Ŀ' => 'L',
        'ĺ' => 'l',
        'ľ' => 'l',
        'ŀ' => 'l',
        'Ñ' => 'N',
        'ñ' => 'n',
        'ŉ' => 'n',
        'Ò' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ō' => 'O',
        'Ŏ' => 'O',
        'Ǒ' => 'O',
        'Ő' => 'O',
        'Ơ' => 'O',
        'Ø' => 'O',
        'Ǿ' => 'O',
        'Œ' => 'OE',
        'ò' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ō' => 'o',
        'ŏ' => 'o',
        'ǒ' => 'o',
        'ő' => 'o',
        'ơ' => 'o',
        'ø' => 'o',
        'ǿ' => 'o',
        'º' => 'o',
        'œ' => 'oe',
        'Ŕ' => 'R',
        'Ŗ' => 'R',
        'ŕ' => 'r',
        'ŗ' => 'r',
        'Ŝ' => 'S',
        'Ș' => 'S',
        'ŝ' => 's',
        'ș' => 's',
        'ſ' => 's',
        'Ţ' => 'T',
        'Ț' => 'T',
        'Ŧ' => 'T',
        'Þ' => 'TH',
        'ţ' => 't',
        'ț' => 't',
        'ŧ' => 't',
        'þ' => 'th',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ũ' => 'U',
        'Ŭ' => 'U',
        'Ű' => 'U',
        'Ų' => 'U',
        'Ư' => 'U',
        'Ǔ' => 'U',
        'Ǖ' => 'U',
        'Ǘ' => 'U',
        'Ǚ' => 'U',
        'Ǜ' => 'U',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ũ' => 'u',
        'ŭ' => 'u',
        'ű' => 'u',
        'ų' => 'u',
        'ư' => 'u',
        'ǔ' => 'u',
        'ǖ' => 'u',
        'ǘ' => 'u',
        'ǚ' => 'u',
        'ǜ' => 'u',
        'Ŵ' => 'W',
        'ŵ' => 'w',
        'Ý' => 'Y',
        'Ÿ' => 'Y',
        'Ŷ' => 'Y',
        'ý' => 'y',
        'ÿ' => 'y',
        'ŷ' => 'y',

        // Russian
        'Ъ' => '',
        'Ь' => '',
        'А' => 'A',
        'Б' => 'B',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Э' => 'E',
        'Ф' => 'F',
        'Г' => 'G',
        'Х' => 'H',
        'И' => 'I',
        'Й' => 'J',
        'Я' => 'Ja',
        'Ю' => 'Ju',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Ш' => 'Sh',
        'Щ' => 'Shch',
        'Т' => 'T',
        'У' => 'U',
        'В' => 'V',
        'Ы' => 'Y',
        'З' => 'Z',
        'Ж' => 'Zh',
        'ъ' => '',
        'ь' => '',
        'а' => 'a',
        'б' => 'b',
        'ц' => 'c',
        'ч' => 'ch',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'э' => 'e',
        'ф' => 'f',
        'г' => 'g',
        'х' => 'h',
        'и' => 'i',
        'й' => 'j',
        'я' => 'ja',
        'ю' => 'ju',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'ш' => 'sh',
        'щ' => 'shch',
        'т' => 't',
        'у' => 'u',
        'в' => 'v',
        'ы' => 'y',
        'з' => 'z',
        'ж' => 'zh',

        // German characters
        'Ä' => 'AE',
        'Ö' => 'OE',
        'Ü' => 'UE',
        'ß' => 'ss',
        'ä' => 'ae',
        'ö' => 'oe',
        'ü' => 'ue',

        // Turkish characters
        'Ç' => 'C',
        'Ğ' => 'G',
        'İ' => 'I',
        'Ş' => 'S',
        'ç' => 'c',
        'ğ' => 'g',
        'ı' => 'i',
        'ş' => 's',

        // Latvian
        'Ā' => 'A',
        'Ē' => 'E',
        'Ģ' => 'G',
        'Ī' => 'I',
        'Ķ' => 'K',
        'Ļ' => 'L',
        'Ņ' => 'N',
        'Ū' => 'U',
        'ā' => 'a',
        'ē' => 'e',
        'ģ' => 'g',
        'ī' => 'i',
        'ķ' => 'k',
        'ļ' => 'l',
        'ņ' => 'n',
        'ū' => 'u',

        // Ukrainian
        'Ґ' => 'G',
        'І' => 'I',
        'Ї' => 'Ji',
        'Є' => 'Ye',
        'ґ' => 'g',
        'і' => 'i',
        'ї' => 'ji',
        'є' => 'ye',

        // Czech
        'Č' => 'C',
        'Ď' => 'Dj',
        'Ě' => 'E',
        'Ň' => 'N',
        'Ř' => 'R',
        'Š' => 'S',
        'Ť' => 'T',
        'Ů' => 'U',
        'Ž' => 'Z',
        'č' => 'c',
        'ď' => 'dj',
        'ě' => 'e',
        'ň' => 'n',
        'ř' => 'r',
        'š' => 's',
        'ť' => 't',
        'ů' => 'u',
        'ž' => 'z',

        // Polish
        'Ą' => 'A',
        'Ć' => 'C',
        'Ę' => 'E',
        'Ł' => 'L',
        'Ń' => 'N',
        'Ó' => 'O',
        'Ś' => 'S',
        'Ź' => 'Z',
        'Ż' => 'Z',
        'ą' => 'a',
        'ć' => 'c',
        'ę' => 'e',
        'ł' => 'l',
        'ń' => 'n',
        'ó' => 'o',
        'ś' => 's',
        'ź' => 'z',
        'ż' => 'z',

        // Greek
        'Α' => 'A',
        'Β' => 'B',
        'Γ' => 'G',
        'Δ' => 'D',
        'Ε' => 'E',
        'Ζ' => 'Z',
        'Η' => 'E',
        'Θ' => 'Th',
        'Ι' => 'I',
        'Κ' => 'K',
        'Λ' => 'L',
        'Μ' => 'M',
        'Ν' => 'N',
        'Ξ' => 'X',
        'Ο' => 'O',
        'Π' => 'P',
        'Ρ' => 'R',
        'Σ' => 'S',
        'Τ' => 'T',
        'Υ' => 'Y',
        'Φ' => 'Ph',
        'Χ' => 'Ch',
        'Ψ' => 'Ps',
        'Ω' => 'O',
        'Ϊ' => 'I',
        'Ϋ' => 'Y',
        'ά' => 'a',
        'έ' => 'e',
        'ή' => 'e',
        'ί' => 'i',
        'ΰ' => 'Y',
        'α' => 'a',
        'β' => 'b',
        'γ' => 'g',
        'δ' => 'd',
        'ε' => 'e',
        'ζ' => 'z',
        'η' => 'e',
        'θ' => 'th',
        'ι' => 'i',
        'κ' => 'k',
        'λ' => 'l',
        'μ' => 'm',
        'ν' => 'n',
        'ξ' => 'x',
        'ο' => 'o',
        'π' => 'p',
        'ρ' => 'r',
        'ς' => 's',
        'σ' => 's',
        'τ' => 't',
        'υ' => 'y',
        'φ' => 'ph',
        'χ' => 'ch',
        'ψ' => 'ps',
        'ω' => 'o',
        'ϊ' => 'i',
        'ϋ' => 'y',
        'ό' => 'o',
        'ύ' => 'y',
        'ώ' => 'o',
        'ϐ' => 'b',
        'ϑ' => 'th',
        'ϒ' => 'Y',

        // Esperanto
        'ĉ' => 'cx',
        'ĝ' => 'gx',
        'ĥ' => 'hx',
        'ĵ' => 'jx',
        'ŝ' => 'sx',
        'ŭ' => 'ux',
        'Ĉ' => 'CX',
        'Ĝ' => 'GX',
        'Ĥ' => 'HX',
        'Ĵ' => 'JX',
        'Ŝ' => 'SX',
        'Ŭ' => 'UX',

        /* Arabic */
        'أ' => 'a',
        'ب' => 'b',
        'ت' => 't',
        'ث' => 'th',
        'ج' => 'g',
        'ح' => 'h',
        'خ' => 'kh',
        'د' => 'd',
        'ذ' => 'th',
        'ر' => 'r',
        'ز' => 'z',
        'س' => 's',
        'ش' => 'sh',
        'ص' => 's',
        'ض' => 'd',
        'ط' => 't',
        'ظ' => 'th',
        'ع' => 'aa',
        'غ' => 'gh',
        'ف' => 'f',
        'ق' => 'k',
        'ك' => 'k',
        'ل' => 'l',
        'م' => 'm',
        'ن' => 'n',
        'ه' => 'h',
        'و' => 'o',
        'ي' => 'y'
        );
        
        $string = strtolower(strtr($string, $rules));
        $string = preg_replace('/([^a-z0-9]|-)+/', $separator, $string);
        $string = strtolower($string);

        return trim($string, $separator);
    }
    
    public function countryName($countryCode, $locale = 'fr') {
        $c = \Symfony\Component\Locale\Locale::getDisplayCountries($locale);
        
        return array_key_exists( $countryCode, $c ) ? $c[$countryCode] : $countryCode;
    }
    
    
    public function getName()
    {
        return 'solerni_twig_extension';
    }
}