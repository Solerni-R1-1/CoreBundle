<?php


namespace Claroline\CoreBundle\Form\Contact;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Translation\TranslatorInterface;

class ContactServiceType extends AbstractType
{   
    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;
    

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add(
                    'name', 
                    'text',
                    array(
                        'attr' => array('placeholder' => $this->translator->trans('contact_name', array(), 'plateform')),
                        'required' => true,
                        'error_bubbling' => true
                    )
                 )
                ->add(
                    'mail',
                    'email',
                    array(
                        'attr' => array('placeholder' => $this->translator->trans('contact_mail', array(), 'plateform')),
                        'required' => true,
                        'constraints' => new Email(),
                        'error_bubbling' => true 
                    )
                );
    }

    public function getName()
    {
        return 'contact_services_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'data_class' => 'Claroline\CoreBundle\Entity\Contact\Contact',
                'translation_domain' => 'platform'
                )
        );
    }
}
