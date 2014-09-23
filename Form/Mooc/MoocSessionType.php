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
                        'label' => 'session_start_date',
                        'widget' => 'single_text',
                        'required' => true,
                        'attr' => array( 'class' => 'slrn-date'),
                        'format' => 'dd/MM/yyyy'
                    )
                )
            ->add('endDate',
                    'date', 
                    array(
                        'label' => 'session_end_date',
                        'widget' => 'single_text',
                        'required' => true,
                        'attr' => array( 'class' => 'slrn-date'),
                        'format' => 'dd/MM/yyyy'
                    )
                )
            ->add('startInscriptionDate', 
                    'date', 
                    array(
                        'label' => 'session_start_subscription',
                        'widget' => 'single_text',
                        'required' => true,
                        'attr' => array( 'class' => 'slrn-date'),
                        'format' => 'dd/MM/yyyy'
                    )
                )
            ->add('endInscriptionDate', 
                    'date',
                    array(
                        'label' => 'session_end_subscription',
                        'widget' => 'single_text',
                        'required' => true,
                        'attr' => array( 'class' => 'slrn-date'),
                        'format' => 'dd/MM/yyyy'
                    )
                )
            ->add('title', 
                    'text', 
                    array(
                        'label' => 'session_title',
                        'required' => true
                    )
                )
            ->add('maxUsers', 
                    'integer', 
                    array(
                        'label' => 'session_max_users',
                        'required' => false
                    ))
            ->add('forum', 'entity', array(
                'label' => 'Forum',
                'property' => 'name',
                'empty_value' => 'session_choose_forum',
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
            'translation_domain' => 'platform',
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
