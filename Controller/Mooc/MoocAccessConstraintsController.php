<?php

namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;
use Claroline\CoreBundle\Form\Mooc\MoocAccessConstraintsType;
use JMS\SecurityExtraBundle\Annotation\Secure;


/**
 * Mooc\MoocAccessConstraints controller.
 */
class MoocAccessConstraintsController extends Controller
{

    /**
     * Lists all Mooc\MoocAccessConstraints entities.
     *
     * @Route("/", name="admin_parameters_mooc_accessconstraints")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints')->findAll();
        
        $forms = array();
        
        foreach( $entities as $entity ) {
            $deleteForm = $this->createDeleteForm( $entity->getId());
            $forms[] = $deleteForm->createView();
        }

        return array(
            'entities' => $entities,
            'forms'    => $forms
        );
    }
    /**
     * Creates a new Mooc\MoocAccessConstraints entity.
     *
     * @Route("/", name="admin_parameters_mooc_accessconstraints_create")
     * @Method("POST")
     * @Template("ClarolineCoreBundle:Mooc\MoocAccessConstraints:new.html.twig")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function createAction(Request $request)
    {
        $entity = new MoocAccessConstraints();
        $form = $this->createCreateForm( $entity );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_parameters_mooc_accessconstraints', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Mooc\MoocAccessConstraints entity.
     *
     * @param MoocAccessConstraints $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm( MoocAccessConstraints $entity)
    {
        $form = $this->createForm(new MoocAccessConstraintsType(), $entity, array(
            'action' => $this->generateUrl('admin_parameters_mooc_accessconstraints_create'),
            'method' => 'POST',
        ));

        $form->add('save', 'submit', array('label' => 'Create', 'attr' => array('class' => 'hide')));

        return $form;
    }

    /**
     * Displays a form to create a new Mooc\MoocAccessConstraints entity.
     *
     * @Route("/new", name="admin_parameters_mooc_accessconstraints_new")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     */
    public function newAction()
    {
        $entity = new MoocAccessConstraints();
        $form   = $this->createCreateForm( $entity );

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Mooc\MoocAccessConstraints entity.
     *
     * @Route("/{id}/edit", name="admin_parameters_mooc_accessconstraints_edit")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     */
    public function editAction( $id )
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mooc\MoocAccessConstraints entity.');
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
    * Creates a form to edit a Mooc\MoocAccessConstraints entity.
    *
    * @param MoocAccessConstraints $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(MoocAccessConstraints $entity)
    {
        $form = $this->createForm(new MoocAccessConstraintsType(), $entity, array(
            'action' => $this->generateUrl('admin_parameters_mooc_accessconstraints_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('save', 'submit', array('label' => 'Update', 'attr' => array('class' => 'hide')));

        return $form;
    }
    /**
     * Edits an existing Mooc\MoocAccessConstraints entity.
     *
     * @Route("/{id}", name="admin_parameters_mooc_accessconstraints_update")
     * @Method("PUT")
     * @Template("ClarolineCoreBundle:Mooc\MoocAccessConstraints:edit.html.twig")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mooc\MoocAccessConstraints entity.');
        }

        $deleteForm = $this->createDeleteForm( $id );
        $editForm = $this->createEditForm( $entity );
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            
            foreach ( $entity->getMoocs() as $mooc ) {
                foreach ( $mooc->getMoocSessions() as $session ) {
                    //TODO By the listener
                    $this->get('orange.search.indexer_todo_manager')
                         ->toIndex($session);
                }
            }
            
            $em->flush();

            return $this->redirect($this->generateUrl('admin_parameters_mooc_accessconstraints', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Mooc\MoocAccessConstraints entity.
     *
     * @Route("/{id}", name="admin_parameters_mooc_accessconstraints_delete")
     * @Method("DELETE")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Mooc\MoocAccessConstraints entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_parameters_mooc_accessconstraints'));
    }

    /**
     * Creates a form to delete a Mooc\MoocAccessConstraints entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_parameters_mooc_accessconstraints_delete', array( 'id' => $id )))
            ->setMethod('DELETE')
            ->add('save', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
     
}