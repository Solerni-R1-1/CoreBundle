<?php

namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Claroline\CoreBundle\Entity\Mooc\MoocCategory;
use Claroline\CoreBundle\Form\Mooc\MoocCategoryType;

class MoocCategoryController extends Controller
{

    /**
     * Lists all Mooc\MoocCategory entities.
     *
     * @Route("/", name="admin_parameters_mooc_categories")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClarolineCoreBundle:Mooc\MoocCategory')->findAll();
        
        $forms = array();
        
        foreach( $entities as $entity ) {
            $deleteForm = $this->createDeleteForm( $entity->getId() );
            $forms[] = $deleteForm->createView();
        }

        return array(
            'entities'  => $entities,
            'forms' => $forms
        );
    }
    /**
     * Creates a new Mooc\MoocCategory entity.
     *
     * @Route("/", name="admin_parameters_mooc_category_create")
     * @Method("POST")
     * @Template("ClarolineCoreBundle:Mooc\MoocCategory:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new MoocCategory();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_parameters_mooc_categories'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Mooc\MoocCategory entity.
     *
     * @param MoocCategory $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(MoocCategory $entity)
    {
        $form = $this->createForm(new MoocCategoryType(), $entity, array(
            'action' => $this->generateUrl('admin_parameters_mooc_category_create'),
            'method' => 'POST',
        ));

        $form->add('save', 'submit', array('label' => 'Create', 'attr' => array ( 'class' => 'hide' ) ));

        return $form;
    }

    /**
     * Displays a form to create a new Mooc\MoocCategory entity.
     *
     * @Route("/new", name="admin_parameters_mooc_category_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new MoocCategory();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Mooc\MoocCategory entity.
     *
     * @Route("/{id}", name="admin_parameters_mooc_category_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mooc\MoocCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Mooc\MoocCategory entity.
     *
     * @Route("/{id}/edit", name="admin_parameters_mooc_category_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mooc\MoocCategory entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Mooc\MoocCategory entity.
    *
    * @param MoocCategory $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(MoocCategory $entity)
    {
        $form = $this->createForm(new MoocCategoryType(), $entity, array(
            'action' => $this->generateUrl('admin_parameters_mooc_category_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('save', 'submit', array('label' => 'Update', 'attr' => array( 'class' => 'hide' ) ));

        return $form;
    }
    /**
     * Edits an existing Mooc\MoocCategory entity.
     *
     * @Route("/{id}", name="admin_parameters_mooc_category_update")
     * @Method("PUT")
     * @Template("ClarolineCoreBundle:Mooc\MoocCategory:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mooc\MoocCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_parameters_mooc_categories'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Mooc\MoocCategory entity.
     *
     * @Route("/{id}", name="admin_parameters_mooc_category_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocCategory')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Mooc\MoocCategory entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_parameters_mooc_categories'));
    }

    /**
     * Creates a form to delete a Mooc\MoocCategory entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_parameters_mooc_category_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('save', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
