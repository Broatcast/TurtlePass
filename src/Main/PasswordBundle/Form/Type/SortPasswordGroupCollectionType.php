<?php

namespace Main\PasswordBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;

class SortPasswordGroupCollectionType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sorting', CollectionType::class, [
                'required' => true,
                'entry_type' => SortPasswordGroupType::class,
                'allow_add' => true,
                'constraints' => [
                    new Valid(),
                ],
            ])
        ;
    }
}
