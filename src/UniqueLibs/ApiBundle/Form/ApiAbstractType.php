<?php

namespace UniqueLibs\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ApiAbstractType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string|null
     */
    public function getBlockPrefix()
    {
        return null;
    }
}
