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
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageSimpleType extends AbstractType
{

    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /**
     * @DI\InjectParams({
     *     "router"     = @DI\Inject("router"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content',
                'tinymce',
                array(
                    'attr' => array(),
                    'required' => true,
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            );
    }

    public function getName()
    {
        return 'message_simple_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
