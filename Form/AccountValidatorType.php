<?php

/*
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;

class AccountValidatorType extends AbstractType
{
	private $user;

	public function __construct($user){
		$this->user = $user;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mail','email',array('required' => true,
        									'constraints' => new Email(), 
        									'label' => 'email', 
        									'data' => $this->user->getMail(),
        									'read_only' => true
        								));
        $builder->add('keyValidate','text',array('required' => true,'label' => 'key'));
        $builder->add('valider', 'submit');
    }

    public function getName()
    {
        return 'account_validator_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
