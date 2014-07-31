<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;


class TransfoController extends Controller
{

    /**
     * @EXT\Route(
     *     "transfo/{filters}",
     *     name="claro_transfo_fltr"
     * )
     * @EXT\Route(
     *     "transfo",
     *     name="claro_transfo"
     * )
     * @EXT\Method("GET")
     *
     * @return Response
     */
    public function transfoAction($filters = "")
    {

        $imageURI = $this->get('request')->get('img_uri');

        if (@fopen($imageURI, "r")) {
            $image = $this->filter($this->get('image.handling')->open($imageURI), $filters);
            $image_mime = image_type_to_mime_type(exif_imagetype($imageURI));
            /**
             *  To Do :
             *  $ds = DIRECTORY_SEPARATOR;
             *  $webDir = "{$this->container->get('kernel')->getRootDir()}{$ds}..{$ds}web";
             *  $cacheData = file_get_contents($webDir.$image->cacheFile('guess'));
             */
            $cacheData = file_get_contents('http://' . $this->getRequest()->getHost() . $image->cacheFile('guess'));
            if ( $cacheData ) {
                $response = new Response($cacheData);
                $response->headers->set('Content-Type', $image_mime);
                $response->setPublic(); 
            } else {
                $response = new Response();
                $response->setStatusCode(404);  
            }
        } else {
            $response = new Response();
            $response->setStatusCode(404);
        }
        return $response;
    }
    
    /**
     * Tranform image with filters
     * 
     * @param Image $image image to transform
     * @param string $filters filters to applicate
     * 
     * @return Image image
     */
    private function filter($image, $filters)
    {
        $filters = explode(",", $filters);
        foreach ($filters as $filter) {
            $filterElmnts = explode("_", $filter);
            $functionShortCut = array_shift($filterElmnts);
            $functionName = $this->findFunctionFilterName($functionShortCut);
            if ($functionName) {
                try {
                    $image = call_user_func_array(array($image, $functionName), $filterElmnts);
                } catch (\Exception $ex) {
                    $this->get('logger')->error($ex->getMessage());
                }
            }
        }
        return $image;
    }

    /**
     * Get methode name by short cut
     * 
     * @param string $filter short cut
     * 
     * @return string method name
     */
    private function findFunctionFilterName($filter)
    {
        switch ($filter) {
            case 'gs':
                return 'grayscale';
            case 'rot':
                return 'rotate';
            case 'zcrop':
                return 'zoomCrop';
            case 'n':
                //negate()
                return 'negate';
            case 'b':
                //brightness($brightness)
                return 'brightness';
            case 'c':
                //contrast($contrast)
                return 'contrast';
            case 'clr':
                //colorize($red, $green, $blue)
                return 'colorize';
            case 'sep':
                //sepia()
                return 'sepia';
            default:
                return false;
        }
    }

}
