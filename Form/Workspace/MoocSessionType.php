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
            ->add('startDate', 
                    'date', 
                    array( 
                        'label' => 'Début de session',
                        'widget' => 'single_text',
                        'required' => false
                    )
                )
            ->add('endDate',
                    'date', 
                    array(
                        'label' => 'Fin de session',
                        'widget' => 'single_text',
                        'required' => false
                    )
                )
            ->add('startInscriptionDate', 
                    'date', 
                    array(
                        'label' => 'Début d\'inscription',
                        'widget' => 'single_text',
                        'required' => false
                    )
                )
            ->add('endInscriptionDate', 
                    'date',
                    array(
                        'label' => 'Fin d\'inscription',
                        'widget' => 'single_text',
                        'required' => false
                    )
                )
            ->add('title', 
                    'text', 
                    array(
                        'label' => 'Titre de la session',
                        'required' => false
                    )
                )
            ->add('maxUsers', 
                    'integer', 
                    array(
                        'label' => 'Nombre maximum d\'inscrits',
                        'required' => false
                    ))
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
