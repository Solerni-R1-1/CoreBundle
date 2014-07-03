<?php

namespace Claroline\CoreBundle\Form\Mooc;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoocSessionType extends AbstractType
{
    
    private $forumResourceType;
    private $workspace;

    public function __construct( $workspace, $forumResourceType ) {
        $this->forumResourceType = $forumResourceType;
        $this->workspace = $workspace;
    }
    
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
            ->add('forum', 'entity', array(
                'label' => 'Forum',
                'property' => 'name',
                'empty_value' => '-- Choisir un forum de session --',
                'class' => 'ClarolineCoreBundle:Resource\ResourceNode',
                'required' => false,
                    'query_builder' => function ( \Doctrine\ORM\EntityRepository $er )  {
                            return $er->getQueryFindByWorkspaceAndResourceType($this->workspace, $this->forumResourceType);
                    }
            ))
            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Claroline\CoreBundle\Entity\Mooc\MoocSession',
            'language' => 'fr'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'claroline_corebundle_moocsession';
    }
}
