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
use Symfony\Component\Validator\Constraints\NotBlank;
use Claroline\CoreBundle\Validator\Constraints\SendToNames;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MessageType extends AbstractType
{
    private $username;
    private $object;


    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /**
     * @DI\InjectParams({
     *     "router"     = @DI\Inject("router"),
     *     "translator" = @DI\Inject("translator")
     * })
     *
     * @param string $username
     * @param string $object
     */
    public function __construct($username = null, $object = null, TranslatorInterface $translator)
    {
        $this->username = $username;
        $this->object = $object;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'to',
                'text',
                array(
                    'attr' => array('placeholder'    => $this->translator->trans('message_form_to', array(), 'plateform')),
                    'data' => $this->username,
                    'required' => true,
                    'mapped' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new SendToNames()
                    )
                )
                 
            )
            ->add(
                'object',
                'text',
                array(
                    'attr' => array('placeholder'    => $this->translator->trans('message_form_object', array(), 'plateform')),
                    'data' => $this->object, 
                    'required' => true
                )
            )
            ->add(
                'content',
                'tinymce',
                array(
                    'attr' => array('placeholder'    => 'ss'),
                    'required' => true
                )
            );
    }

    public function getName()
    {
        return 'message_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
