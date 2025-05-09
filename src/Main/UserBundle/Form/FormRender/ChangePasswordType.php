<?php

namespace Main\UserBundle\Form\FormRender;

use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

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
                'required' => true,
                'property_path' => 'currentPassword',
                'translation_domain' => false,
                'attr' => ['ng-minlength' => 8, 'ng-maxlength' => 64],
                'label_attr' => ['translate' => 'USER.CHANGE_PASSWORD.CURRENT_PASSWORD'],
            ])
            ->add('new_password', PasswordType::class, [
                'required' => true,
                'property_path' => 'newPassword',
                'translation_domain' => false,
                'attr' => ['ng-minlength' => 8, 'ng-maxlength' => 64],
                'label_attr' => ['translate' => 'USER.CHANGE_PASSWORD.NEW_PASSWORD'],
            ])
            ->add('new_password_repeat', PasswordType::class, [
                'required' => true,
                'property_path' => 'newPasswordRepeat',
                'translation_domain' => false,
                'attr' => ['ng-minlength' => 8, 'ng-maxlength' => 64],
                'label_attr' => ['translate' => 'USER.CHANGE_PASSWORD.NEW_PASSWORD_REPEAT'],
            ])
        ;
    }
}
