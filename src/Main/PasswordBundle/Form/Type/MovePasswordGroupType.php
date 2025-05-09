<?php

namespace Main\PasswordBundle\Form\Type;

use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Main\PasswordBundle\Entity\PasswordGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovePasswordGroupType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent', EntityType::class, [
                'class' => 'Main\PasswordBundle\Entity\PasswordGroup',
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => PasswordGroup::class,
        ]);
    }
}
