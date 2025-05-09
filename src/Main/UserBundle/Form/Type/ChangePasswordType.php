<?php

namespace Main\UserBundle\Form\Type;

use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Main\UserBundle\Form\Model\ChangePasswordModel;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('current_password', PasswordType::class, [
                'property_path' => 'currentPassword',
            ])
            ->add('new_password', PasswordType::class, [
                'property_path' => 'newPassword',
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
            'data_class' => ChangePasswordModel::class,
        ]);
    }
}
