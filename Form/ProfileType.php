<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Image;
use Claroline\CoreBundle\Entity\User;

class ProfileType extends AbstractType
{
    private $platformRoles;
    private $isAdmin;
    private $langs;

    /**
     * Constructor.
     *
     * @param Role[]   $platformRoles
     * @param boolean  $isAdmin
     * @param string[] $langs
     */
    public function __construct(array $platformRoles, $isAdmin, array $langs)
    {
        $this->platformRoles = new ArrayCollection($platformRoles);
        $this->isAdmin = $isAdmin;

        if (!empty($langs)) {
            $this->langs = $langs;
        } else {
            $this->langs = array('en' => 'en', 'fr' => 'fr');
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('firstName', 'text', array('label' => 'First name'))
            ->add('lastName', 'text', array('label' => 'Last name'))
            ->add('username', 'text', array('label' => 'user_form_username'))
            ->add('administrativeCode', 'text', array(
            		'required' => false,
            		'read_only' => true,
            		'disabled' => true,
            		'label' => 'administrative_code')
            )
            ->add('publicUrl','text', array('required' => true, 'label' => 'user_form_public_url' ))
            ->add('mail', 'email', array('read_only' => true, 'disabled' => true, 'required' => false, 'label' => 'email'))
            ->add('phone', 'text', array('required' => false, 'label' => 'phone'))
            ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false, 'label' => 'Language'));

        if (!$this->isAdmin) {
            $builder->add(
                    'accepted_com_terms',
                    'checkbox',
                    array(
                        'label' => 'I agree that my personal information be used for commercial purposes',
                        'required' => false
                ));
        } else {
            $builder->add(
                    'platformRoles',
                    'entity',
                    array(
                        'mapped' => false,
                        'data' => $this->platformRoles,
                        'class' => 'Claroline\CoreBundle\Entity\Role',
                        'expanded' => true,
                        'multiple' => true,
                        'property' => 'translationKey',
                        'query_builder' => function (RoleRepository $roleRepository) {
                            return $roleRepository->createQueryBuilder('r')
                                    ->where("r.type != " . Role::WS_ROLE)
                                    ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                        },
                        'label' => 'roles'
                    )
                );
        }
        $builder->add(
            'pictureFile',
            'file',
            array(
                'required' => false,
                'constraints' => new Image(
                    array(
                        'minWidth'  => 50,
                        'maxWidth'  => 2048,
                        'minHeight' => 50,
                        'maxHeight' => 2048,
                    )
                ),
                'label' => 'picture_profile'
            )
        )
	        ->add('picture','text', array('label' => 'picture'))
	        ->add('gender', 'choice', array(
	        	'choices' => array(
	        			User::GENDER_FEMALE => "female",
	        			User::GENDER_MALE => "male",
	        			User::GENDER_UNKNOWN => "no_tell"
	        		),
	        	'expanded' => true
	        	)
	        )
        	->add('city', 'text', array('required' => false))
        	->add('country', 'country', array('required' => false))
        	->add('birthdate', 'date', array(
                'required' => false,
                'widget' => 'single_text',
                'widget' => 'single_text',
                'attr' => array( 'class' => 'slrn-date', 'placeholder' => 'jj/mm/aaaa'),
                'format' => 'dd/MM/yyyy'))
        	->add('website', 'text', array('required' => false))
        	->add('twitter', 'text', array('required' => false))
        	->add('facebook', 'text', array('required' => false))
        	->add('linkedIn', 'text', array('required' => false))
        	->add('googlePlus', 'text', array('required' => false))

	        ->add(
	            'description',
	            'tinymce',
	            array('required' => false, 'label' => 'description')
	        )
            ->add('notifarticle', 'checkbox',  array('label' => 'Recevoir notification article', 'required' => false))
            ->add('notifsujettheme', 'checkbox',  array( 'label' => 'Recevoir notification thème','required' => false))
            ->add('notifciter', 'checkbox',  array( 'label' => 'Recevoir notification citation','required' => false))
            ->add('notiflike', 'checkbox',  array('label' => 'Recevoir notification like', 'required' => false))

        ;

    }

    public function getName()
    {
        return 'profile_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Claroline\CoreBundle\Entity\User',
                'translation_domain' => 'platform'
            )
        );
    }
}
