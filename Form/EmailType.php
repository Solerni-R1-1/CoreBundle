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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Translation\TranslatorInterface;

class EmailType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'mail',
            'email',
            array(
                'required' => true,
                'constraints' => new Email(),
                'attr' => array (   'placeholder' => $this->translator->trans('user_form_mail'),
                                    'class'         => 'slrn-input',
                                    'data-validation' => 'email',
                                    'data-validation-error-msg' => $this->translator->trans('mail_invalid', array(), 'platform'),
                                    'data-validation-event' => 'keyup'
            ))
        );
    }

    public function getName()
    {
        return 'email_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
