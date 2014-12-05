<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Mooc;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class userMoocPreferencesType extends AbstractType
{
    
    private $suffix;
    
    public function __construct( $count = 0 ) {
        $this->suffix = $count;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('visibility', 'checkbox', array( 'required' => false ));
    }

    public function getName()
    {
        return 'userMoocPreferences_form_' . $this->suffix;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform', 'data_class' => 'Claroline\CoreBundle\Entity\Mooc\userMoocPreferences' ));
    }
}
