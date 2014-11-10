<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Contact;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Translation\TranslatorInterface;

class ContactType extends AbstractType
{
	/** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;
	
    private $contacts = array();

    public function __construct(TranslatorInterface $translator, $contacts) {
        $this->translator = $translator;
        $this->contacts = $contacts;

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('contact', 'choice', 
        		array(
        			'empty_value' => 'Choisissez un destinataire',
				    'choices'   => $this->contacts,
				    'required'  => true,
				)
        	)
        	->add(
	            'replyTo',
	            'email',
	            array(
	            	'attr' => array('placeholder' => $this->translator->trans('contact_form_replyTo', array(), 'plateform')),
	                'required' => true,
	                'constraints' => new Email()
	            )
	        )
            ->add(
                'object',
                'text',
                array(
                    'attr' => array('placeholder' => $this->translator->trans('contact_form_object', array(), 'plateform')),
                    //'data' => $this->object, 
                    'required' => true
                )
            )
            ->add(
                'content',
                'tinymce',
                array(
                    'attr' => array(),
                    'required' => true
                )
            );
    }

    public function getName()
    {
        return 'contact_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
