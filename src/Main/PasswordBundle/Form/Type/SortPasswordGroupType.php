<?php

namespace Main\PasswordBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;

class SortPasswordGroupType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password_group_id', IntegerType::class, [
                'required' => true,
                'constraints' => [],
            ])
            ->add('sorting', IntegerType::class, [
                'required' => true,
                'constraints' => [],
            ])
        ;
    }
}
