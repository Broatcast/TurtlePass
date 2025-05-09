<?php

namespace Main\PasswordBundle\Form\Type\PasswordType\CreditCard;

use Main\PasswordBundle\Entity\PasswordType\CreditCardPassword;
use Main\PasswordBundle\Form\Type\PasswordType\AbstractPasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CreditCardPasswordType extends AbstractPasswordType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('card_type', TextType::class, [
                'required' => false,
                'property_path' => 'cardType',
            ])
            ->add('card_number', TextType::class, [
                'required' => false,
                'property_path' => 'cardNumber',
            ])
            ->add('card_cvc', TextType::class, [
                'required' => false,
                'property_path' => 'cardCvc',
            ])
            ->add('valid_from', TextType::class, [
                'required' => false,
                'property_path' => 'validFrom',
            ])
            ->add('valid_to', TextType::class, [
                'required' => false,
                'property_path' => 'validTo',
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
            'data_class' => CreditCardPassword::class,
        ]);
    }
}
