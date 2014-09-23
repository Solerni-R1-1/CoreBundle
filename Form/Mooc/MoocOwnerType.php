<?php

namespace Claroline\CoreBundle\Form\Mooc;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoocOwnerType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array( 'required' => true, 'label_attr' => array ( 'class' => 'align-right' ) ) )
            ->add('description', 'textarea', array( 'required' => true, 'label_attr' => array ( 'class' => 'align-right' ) ) )
            ->add('logoPath', 'text', array( 'required' => false, 'attr' => array( 'class' => 'hide' ), 'label_attr' => array ( 'class' => 'align-right' ) ) )
            ->add('logoFile', 'file', array( 'required' => false, 'label_attr' => array ( 'class' => 'align-right' )))
            ->add('dressingPath', 'text', array( 'required' => false, 'attr' => array( 'class' => 'hide' ), 'label_attr' => array ( 'class' => 'align-right' ) ))
            ->add('dressingFile', 'file', array( 'required' => false, 'label_attr' => array ( 'class' => 'align-right' ) ) )
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Claroline\CoreBundle\Entity\Mooc\MoocOwner',
             'translation_domain' => 'platform'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'claroline_corebundle_mooc_owner';
    }
}
