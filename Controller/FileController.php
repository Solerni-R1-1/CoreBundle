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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Form\FileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\ForumBundle\Entity\Message;

class FileController extends Controller
{
	
	const PREG_FORUM_ROUTE = "/\/forum\/subject\/([0-9]+)\/messages\/page\/([0-9]+)\/max(\/[0-9]+)?/";
    /**
     * @EXT\Route(
     *     "resource/media/{node}",
     *     name="claro_file_get_media",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @param integer $id
     *
     * @return Response
     */
    public function streamMediaAction(ResourceNode $node)
    {
        $collection = new ResourceCollection(array($node));

        $hasAccess = true;
        if (!$this->checkAccess('OPEN', $collection, true)) {
        	// We must check if we come from the forum to let the access to the resource...
        	$hasAccess = false;
        	if (isset($_SERVER['HTTP_REFERER'])) {
        		$refererUrl = $_SERVER['HTTP_REFERER'];
        		$currentUrl = $_SERVER['REQUEST_URI'];
        		$matches = array();
        		if (preg_match(FileController::PREG_FORUM_ROUTE, $refererUrl, $matches) == 1) {
        			$subjectId = $matches[1];
        			$page = $matches[2];
        			$max = (isset($matches[3]) ? $matches[3] : 20);
        			 
        			$forumManager = $this->get('claroline.manager.forum_manager');
        			$messages = $forumManager->getMessagesPagerById($subjectId, $page, $max);
        			foreach ($messages as $message) {
        				/* @var $message Message */
        				if ($message->getCreator()->getId() == $node->getCreator()->getId()) {
        					if (strpos($message->getContent(), $currentUrl) !== FALSE) {
        						$hasAccess = true;
        						break;
        					}
        				}
        			}
        		}
        	}	
        }
        
        if ($hasAccess) {
	        $file = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);
	        $path = $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR
	            . $file->getHashName();
	
	        $response = new StreamedResponse();
	        $response->setCallBack(
	            function () use ($path) {
	                readfile($path);
	            }
	        );
	
	        $response->headers->set('Content-Type', $node->getMimeType());
	
	        return $response;
        } else {
        	throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    /**
     * @EXT\Route(
     *     "/upload/{parent}",
     *     name="claro_file_upload_with_ajax",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a resource from uploaded file.
     *
     * @param integer $parentId the parent id
     *
     * @throws \Exception
     * @return Response
     */
    public function uploadWithAjaxAction(ResourceNode $parent, User $user)
    {
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => 'file'));
        $this->checkAccess('CREATE', $collection);
        $file = new File();
        $request = $this->getRequest();
        $fileName = $request->get('fileName');
        $tmpFile = $request->files->get('file');
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);
        $mimeType = $tmpFile->getClientMimeType();
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid() . '.' . $extension;
        $tmpFile->move($this->container->getParameter('claroline.param.files_directory'), $hashName);
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);
        $manager = $this->get('claroline.manager.resource_manager');
        $file = $manager->create(
            $file,
            $manager->getResourceTypeByName('file'),
            $user,
            $parent->getWorkspace(),
            $parent
        );

        return new JsonResponse(
            array($manager->toArray($file->getResourceNode(),
            $this->get('security.context')->getToken()))
        );
    }

    /**
     * @EXT\Route("uploadmodal", name="claro_upload_modal", options = {"expose" = true})
     *
     * @EXT\Template("ClarolineCoreBundle:Resource:uploadModal.html.twig")
     *
     */
    public function uploadModalAction()
    {
        return array(
            'form' => $this->get('form.factory')->create(new FileType())->createView(),
            'workspace' => $this->get('claroline.manager.resource_manager')->getWorkspaceRoot(
                $this->getCurrentUser()->getPersonalWorkspace()
            )->getId()
        );
    }

    /**
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string             $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, ResourceCollection $collection, $return = false)
    {
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
        	if (!$return) {
            	throw new AccessDeniedException($collection->getErrorsForDisplay());
        	} else {
        		return false;
        	}
        }
        return true;
    }

    /**
     * Get Current User
     *
     * @return mixed Claroline\CoreBundle\Entity\User or null
     */
    private function getCurrentUser()
    {
        if (is_object($token = $this->get('security.context')->getToken()) and is_object($user = $token->getUser())) {
            return $user;
        }
    }
}
