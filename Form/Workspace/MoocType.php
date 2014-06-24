<?php

namespace Claroline\CoreBundle\Form\Workspace;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoocType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('required' => true))
            ->add('alias', 'text', array('required' => false))
            ->add('description','textarea', array('required' => true))
            ->add('file', 'file', array('label' => 'mooc_desc_image', 'required' => true))
            ->add('postEndAction', 'choice', array('choices' => array('empty_value' => 'Choisir une action', '1' => 'Fermer', '2' => 'Supprimer' ), 'required' => false))
            ->add('duration', 'text', array('required' => false))
            ->add('weeklyTime', 'text', array('required' => false))
            ->add('cost', 'integer', array('required' => false))
            ->add('language', 'choice', array('choices' => array('empty_value' => 'Choisir une langue', 'fr_FR' => 'français', 'en_EN' => 'Anglais' ), 'required' => true))
            ->add('hasVideo', 'checkbox', array('required' => false))
            ->add('hasSubtitle', 'checkbox', array('required' => false))
            ->add('prerequisites','tinymce', array('required' => false))
            ->add('teamDescription','tinymce',array('required' => false))
            ->add('hasFacebookShare', 'checkbox', array('required' => false))
            ->add('hasTweeterShare', 'checkbox', array('required' => false))
            ->add('hasGplusShare', 'checkbox', array('required' => false))
            ->add('hasLinkedinShare', 'checkbox', array('required' => false))
            ->add('moocSessions', 'collection', array('type' => new MoocSessionType, 'allow_add' => true, 'by_reference' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Claroline\CoreBundle\Entity\Workspace\Mooc'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'claroline_corebundle_workspace_mooc';
    }
}
