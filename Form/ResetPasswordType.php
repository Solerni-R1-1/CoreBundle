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
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

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
        $builder
            ->add('password', 
                'password',
                array('required' => true, 
                      'label' => 'currentPassword',
                      'attr' => array ('placeholder' => $this->translator->trans('currentPassword'))
                      )
            )
            ->add(
            'plainPassword',
            'repeated',
            array(
                'type' => 'password',
                'invalid_message' => 'password_mismatch',
                'first_options' => array('label' => 'new_password', 
                                            'attr' => array (
                                                'data-name' => 'pass_confirmation',
                                                'placeholder' => 'Mot de passe',
                                                'class' => 'plainPasswordFirstClasses',
                                                'data-validation' => 'length',
                                                'data-validation-length' => 'min4',
                                                'data-validation-error-msg' => $this->translator->trans('user_rules_password', array(), 'platform')
                ) ),
                'second_options' => array('label' => 'repeat_password',
                                            'attr' => array (
                                                'data-name' => 'pass',
                                                'placeholder' => 'Répétez le mot de passe',
                                                'class' => 'plainPasswordFirstClasses',
                                                'data-validation' => 'confirmation',
                                                'data-validation-error-msg' => $this->translator->trans('password_mismatch', array(), 'platform')
                ) )
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
                'translation_domain' => 'platform'
            )
        );
    }
}
