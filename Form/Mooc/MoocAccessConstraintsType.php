<?php

namespace Claroline\CoreBundle\Form\Mooc;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoocAccessConstraintsType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array( 'required' => true, 'label_attr' => array ( 'class' => 'align-right' ) ))
            ->add('moocOwner', 'entity', array( 
                'class' => 'ClarolineCoreBundle:Mooc\MoocOwner',
                'property' => 'name',
                'required' => true,
                'empty_value' => '-- Choisir le propriétaire des règles d\'accès --',
                'label_attr' => array ( 'class' => 'align-right' )
            ) )
            ->add('whitelist', 'textarea', array( 'required' => false, 'label' => 'Liste blanche<span style="font-weight:normal"><br>(1 adresse email par ligne)</span>', 'label_attr' => array ( 'class' => 'align-right' ), 'attr' => array('rows' => 7 ) ))
            ->add('patterns', 'textarea', array( 'required' => false, 'label' => 'Domaines autorisés<span style="font-weight:normal"><br>(@domaine.ext, 1 par ligne)</span>', 'label_attr' => array ( 'class' => 'align-right' ), 'attr' => array('rows' => 7 )  ))

        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints',
             'translation_domain' => 'platform'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'claroline_corebundle_mooc_accessconstraints';
    }
}
