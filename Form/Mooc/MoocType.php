<?php

namespace Claroline\CoreBundle\Form\Mooc;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoocType extends AbstractType
{
        
    private $lessonResourceType;
    private $forumResourceType;
    private $blogResourceType;
    private $workspace;

    public function __construct( $workspace, $lessonResourceType, $forumResourceType, $blogResourceType) {
        $this->lessonResourceType = $lessonResourceType;
        $this->blogResourceType = $blogResourceType;
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
            ->add('alias', 'text', array('required' => true))
            ->add('description','textarea', array('required' => true, 'attr' => array( 'rows' => 6 ) ))
            ->add('owner','entity', array( 
                    'class'     => 'ClarolineCoreBundle:Mooc\MoocOwner',
                    'property'  => 'name',
                    'multiple'  => false,
                    'expanded'  => false,
                    'required'  => true,
                    'empty_value' => 'mooc_choose_owner'
                ))
            ->add('isPublic', 'checkbox', array('required' => false))
            ->add('accessConstraints', 'entity', array(
                    'class' => 'ClarolineCoreBundle:Mooc\MoocAccessConstraints',
                    'property'  => 'name',
                    'multiple'  => true, 
                    'expanded'  => true
                ))
            ->add('categories','entity', array( 
                    'class'     => 'ClarolineCoreBundle:Mooc\MoocCategory',
                    'property'  => 'name',
                    'multiple'  => true,
                    'expanded'  => true,
                    'required'  => true
                ))
            ->add('aboutPageDescription','tinymce', array('required' => false))
            ->add('file', 'file', array('required' => false))
            ->add('illustrationPath', 'text', array('required' => true, 'attr' => array( 'class' => 'hide' )))
            ->add('postEndAction', 'choice', array(
                    'choices' => array(
                        'empty_value' => 'choose_replay_mooc',
                        '1' => 'mooc_can_replay',
                        '2' => 'mooc_cannot_replay'
                    ),
                    'required' => false
                ))
            ->add('duration', 'integer', array('required' => false, 'precision' => 0 ))
            ->add('weeklyTime', 'integer', array('required' => false, 'precision' => 0 ))
            ->add('certificationType', 'choice', array(
                    'choices' => array(
                        'mooc_certif_badge' => 'mooc_certif_badge',
                        'mooc_certif_attestation' => 'mooc_certif_attestation'
                    ),
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true
                ))
            ->add('gratis', 'checkbox', array( 'required' => false ) )
            ->add('cost', 'integer', array('required' => false))
            ->add('language', 'choice', array(
                    'choices' => array(
                        'empty_value' => 'choose_language_mooc',
                        'fr_FR' => 'fr_FR',
                        'en_EN' => 'en_EN'
                    ), 
                    'required' => true
                ))
            ->add('hasVideo', 'checkbox', array('required' => false))
            ->add('hasSubtitle', 'checkbox', array('required' => false))
            ->add('prerequisites','tinymce', array('required' => false))
            ->add('teamDescription','tinymce',array('required' => false))
            ->add('badgesUrl','text',array('required' => false))
            ->add('badgesText','tinymce',array('required' => false))
            ->add('knowledgeBadgesUrl','text',array('required' => false))
            ->add('googleAnalyticsToken','text',array('required' => false, 'disabled' => true))
            ->add('hasFacebookShare', 'checkbox', array('required' => false, 'read_only' => true, 'disabled' => true))
            ->add('hasTweeterShare', 'checkbox', array('required' => false, 'read_only' => true, 'disabled' => true))
            ->add('hasGplusShare', 'checkbox', array('required' => false, 'read_only' => true, 'disabled' => true))
            ->add('hasLinkedinShare', 'checkbox', array('required' => false, 'read_only' => true, 'disabled' => true))
            ->add('lesson', 'entity', array( 
                    'required'      => false,
                    'property'      => 'name',
                    'empty_value'   => 'choose_lesson_mooc',
                    'class'         => 'ClarolineCoreBundle:Resource\ResourceNode',
                    'query_builder' => function ( \Doctrine\ORM\EntityRepository $er )  {
                            return $er->getQueryFindByWorkspaceAndResourceType($this->workspace, $this->lessonResourceType);
                    }
            ))
            ->add('blog', 'entity', array(
            		'required'      => false,
            		'property'      => 'name',
            		'empty_value'   => 'choose_blog_mooc',
            		'class'         => 'ClarolineCoreBundle:Resource\ResourceNode',
            		'query_builder' => function ( \Doctrine\ORM\EntityRepository $er )  {
            			return $er->getQueryFindByWorkspaceAndResourceType($this->workspace, $this->blogResourceType);
            		}
            ))
            ->add('moocSessions', 'collection', array(
                    'type' => new MoocSessionType( $this->workspace, $this->forumResourceType ),
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false
                ))
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
