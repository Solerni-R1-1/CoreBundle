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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ResetPasswordType extends AbstractType
{


    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     *
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add(
            'plainPassword',
            'repeated',
            array(
                'type' => 'password',
                'invalid_message' => 'password_mismatch',
                'first_options' => array('label' => 'new_password'),
                'second_options' => array('label' => 'repeat_password'),
               // 'options' => array('attr' => array('class' => 'plainPasswordFirstClasses'))
                'options' => array( 'attr' => array( 
                            'placeholder' => 'Mot de passe',
                            'class' => 'plainPasswordFirstClasses',
                            'data-validation' => 'length', 
                            'data-validation-length' => 'min4', 
                            'data-name' => 'pass_confirmation', 
                            'data-validation-error-msg' => $this->translator->trans('user_rules_password', array(), 'platform') 
                        ))
                

            )
        );
    }

    public function getName()
    {
        return 'reset_pwd_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Claroline\CoreBundle\Entity\User',
                'translation_domain' => 'platform'
            )
        );
    }
}
