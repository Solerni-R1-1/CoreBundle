<?php

namespace Claroline\CoreBundle\Form\Mooc;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoocType extends AbstractType
{
        
    private $lessonResourceType;
    private $forumResourceType;
    private $workspace;

    public function __construct( $workspace, $lessonResourceType, $forumResourceType ) {
        $this->lessonResourceType = $lessonResourceType;
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
            ->add('title', 'text', array('required' => true))
            ->add('alias', 'text', array('required' => false))
            ->add('description','textarea', array('required' => true))
            ->add('categories','entity', array( 'class' => 'ClarolineCoreBundle:Mooc\MoocCategory', 'property'=> 'name', 'multiple'=> true, 'expanded' => true ))
            ->add('aboutPageDescription','tinymce', array('required' => false))
            ->add('file', 'file', array('required' => false))
            ->add('illustrationPath', 'text', array('required' => true, 'attr' => array( 'class' => 'hide' )))
            ->add('postEndAction', 'choice', array('choices' => array('empty_value' => '-- Choisir une action -- ', '1' => 'Fermer', '2' => 'Supprimer' ), 'required' => false))
            ->add('duration', 'text', array('required' => false))
            ->add('weeklyTime', 'text', array('required' => false))
            ->add('cost', 'integer', array('required' => false))
            ->add('language', 'choice', array('choices' => array('empty_value' => '-- Choisir une langue --', 'fr_FR' => 'Français', 'en_EN' => 'Anglais' ), 'required' => true))
            ->add('hasVideo', 'checkbox', array('required' => false))
            ->add('hasSubtitle', 'checkbox', array('required' => false))
            ->add('prerequisites','tinymce', array('required' => false))
            ->add('teamDescription','tinymce',array('required' => false))
            ->add('hasFacebookShare', 'checkbox', array('required' => false))
            ->add('hasTweeterShare', 'checkbox', array('required' => false))
            ->add('hasGplusShare', 'checkbox', array('required' => false))
            ->add('hasLinkedinShare', 'checkbox', array('required' => false))
            ->add('hasLinkedinShare', 'checkbox', array('required' => false))
            ->add('lesson', 'entity', array( 
                    'required' => false,
                    'property' => 'name',
                    'empty_value' => '-- Choisir un cours pour le mooc --',
                    'class' => 'ClarolineCoreBundle:Resource\ResourceNode',
                    'query_builder' => function ( \Doctrine\ORM\EntityRepository $er )  {
                            return $er->getQueryFindByWorkspaceAndResourceType($this->workspace, $this->lessonResourceType);
                    }
            ))
            ->add('moocSessions', 'collection', array('type' => new MoocSessionType( $this->workspace, $this->forumResourceType ), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Claroline\CoreBundle\Entity\Mooc\Mooc',
             'translation_domain' => 'platform',
            'language' => 'fr'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'claroline_corebundle_mooc';
    }
}
