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
use Symfony\Component\Finder\Finder;

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
        $imageURI = $this->get('request')->get('uri');
        $image = $this->filter($this->get('image.handling')->open($imageURI), $filters);
        $image_mime = image_type_to_mime_type(exif_imagetype($imageURI));
        $cacheData = file_get_contents('http://'.$this->getRequest()->getHost().$image->cacheFile('guess'));
        $response = new Response($cacheData);
        $response->headers->set('Content-Type', $image_mime);
        $response->setPublic();
        return $response;
    }
    
    
    private function filter($image, $filters)
    {
        $filters = explode(",", $filters);
        //var_dump($filters);
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
