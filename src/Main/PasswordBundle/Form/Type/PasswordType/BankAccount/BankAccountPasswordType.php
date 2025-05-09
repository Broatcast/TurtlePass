<?php

namespace Main\PasswordBundle\Form\Type\PasswordType\BankAccount;

use Main\PasswordBundle\Entity\PasswordType\BankAccountPassword;
use Main\PasswordBundle\Form\Type\PasswordType\AbstractPasswordType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BankAccountPasswordType extends AbstractPasswordType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('url', UrlType::class, [
                'required' => false,
            ])
            ->add('username', TextType::class, [
                'required' => false,
            ])
            ->add('bank_name', TextType::class, [
                'required' => false,
                'property_path' => 'bankName',
            ])
            ->add('bank_code', TextType::class, [
                'required' => false,
                'property_path' => 'bankCode',
            ])
            ->add('account_holder', TextType::class, [
                'required' => false,
                'property_path' => 'accountHolder',
            ])
            ->add('account_number', TextType::class, [
                'required' => false,
                'property_path' => 'accountNumber',
            ])
            ->add('iban', TextType::class, [
                'required' => false,
            ])
            ->add('pin', TextType::class, [
                'required' => false,
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
            'data_class' => BankAccountPassword::class,
        ]);
    }
}
