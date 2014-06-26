<?php

namespace Claroline\CoreBundle\Form\Workspace;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoocSessionType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', 'datetime', array( 'label' => 'Date de début', 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('endDate', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('startInscriptionDate', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('endInscriptionDate', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('title', 'text', array('required' => false))
            ->add('maxUsers', 'integer')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Claroline\CoreBundle\Entity\Workspace\MoocSession'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'claroline_corebundle_workspace_moocsession';
    }
}
