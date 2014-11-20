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

    private $civilite = array();

    public function __construct(TranslatorInterface $translator, $contacts, $civilite) {
        $this->translator = $translator;
        asort($contacts);
        $this->contacts = $contacts;
        $this->civilite = $civilite;

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('contact', 'choice', 
        		array(
        			'empty_value' => $this->translator->trans('contact_form_demande', array(), 'platform'),
				    'choices'   => $this->contacts,
				    'required'  => true,
				)
        	)->add('civilite', 'choice', 
                array(
                    'empty_value' => $this->translator->trans('contact_form_civil', array(), 'platform'),
                    'choices'   => $this->civilite,
                    'required'  => false,
                )
            )->add(
                'prenom',
                'text',
                array(
                    'attr' => array('placeholder' => $this->translator->trans('contact_form_prenom', array(), 'platform')),
                    'required'  => true,
                )
            )->add(
                'nom',
                'text',
                array(
                    'attr' => array('placeholder' => $this->translator->trans('contact_form_nom', array(), 'platform')),
                    'required'  => true,
                )
            )->add(
                'replyTo',
                'email',
                array(
                    'attr' => array('placeholder' => $this->translator->trans('contact_form_replyTo', array(), 'platform')),
                    'required' => true,
                    'constraints' => new Email()
                )
            )->add(
                'societe',
                'text',
                array(
                    'attr' => array('placeholder' => $this->translator->trans('contact_form_societe', array(), 'platform')),
                    'required'  => false,
                )
            )->add(
                'telephone',
                'text',
                array(
                    'attr' => array('placeholder' => $this->translator->trans('contact_form_telephone', array(), 'platform')),
                    'required'  => false,
                )
            )->add(
                'fonction',
                'text',
                array(
                    'attr' => array('placeholder' => $this->translator->trans('contact_form_fonction', array(), 'platform')),
                    'required'  => false,
                )
            )
            ->add(
                'content',
                'textarea',
                array(
                    'attr' => array('class' => 'contact_text'),
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
