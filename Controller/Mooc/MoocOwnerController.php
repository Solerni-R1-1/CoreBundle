<?php

namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Claroline\CoreBundle\Entity\Mooc\MoocOwner;
use Claroline\CoreBundle\Form\Mooc\MoocOwnerType;

class MoocOwnerController extends Controller
{

    /**
     * Lists all Mooc\MoocOwner entities.
     *
     * @Route("/", name="admin_parameters_mooc_owners")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClarolineCoreBundle:Mooc\MoocOwner')->findAll();
        
         $forms = array();
        
        foreach( $entities as $entity ) {
            $deleteForm = $this->createDeleteForm( $entity->getId() );
            $forms[] = $deleteForm->createView();
        }


        return array(
            'entities' => $entities,
            'forms' => $forms
        );
    }
    /**
     * Creates a new Mooc\MoocOwner entity.
     *
     * @Route("/", name="admin_parameters_mooc_owner_create")
     * @Method("POST")
     * @Template("ClarolineCoreBundle:Mooc\MoocOwner:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new MoocOwner();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_parameters_mooc_owners'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Mooc\MoocOwner entity.
     *
     * @param MoocOwner $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(MoocOwner $entity)
    {
        $form = $this->createForm(new MoocOwnerType(), $entity, array(
            'action' => $this->generateUrl('admin_parameters_mooc_owner_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Mooc\MoocOwner entity.
     *
     * @Route("/new", name="admin_parameters_mooc_owner_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new MoocOwner();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Mooc\MoocOwner entity.
     *
     * @Route("/{id}", name="admin_parameters_mooc_owner_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocOwner')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mooc\MoocOwner entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Mooc\MoocOwner entity.
     *
     * @Route("/{id}/edit", name="admin_parameters_mooc_owner_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocOwner')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mooc\MoocOwner entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'logo'        => $entity->getLogoWebPath(),
            'habillage'   => $entity->getDressingWebPath()
        );
    }

    /**
    * Creates a form to edit a Mooc\MoocOwner entity.
    *
    * @param MoocOwner $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(MoocOwner $entity)
    {
        $form = $this->createForm(new MoocOwnerType(), $entity, array(
            'action' => $this->generateUrl('admin_parameters_mooc_owner_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Mooc\MoocOwner entity.
     *
     * @Route("/{id}", name="admin_parameters_mooc_owner_update")
     * @Method("PUT")
     * @Template("ClarolineCoreBundle:Mooc\MoocOwner:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocOwner')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mooc\MoocOwner entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_parameters_mooc_owners', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Mooc\MoocOwner entity.
     *
     * @Route("/{id}", name="admin_parameters_mooc_owner_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocOwner')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Mooc\MoocOwner entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_parameters_mooc_owners'));
    }

    /**
     * Creates a form to delete a Mooc\MoocOwner entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_parameters_mooc_owner_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
