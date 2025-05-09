<?php

namespace Main\PasswordBundle\Form\Type\PasswordType\Email;

use Main\PasswordBundle\Entity\PasswordType\EmailPassword;
use Main\PasswordBundle\Form\Type\PasswordType\AbstractPasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class EmailPasswordType extends AbstractPasswordType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('email_type', TextType::class, [
                'required' => false,
                'property_path' => 'emailType',
            ])
            ->add('auth_method', TextType::class, [
                'required' => false,
                'property_path' => 'authMethod',
            ])
            ->add('host', TextType::class, [
                'required' => false,
            ])
            ->add('port', TextType::class, [
                'required' => false,
            ])
            ->add('username', TextType::class, [
                'required' => false,
            ])
            ->add('password', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('smtp_auth_method', TextType::class, [
                'required' => false,
                'property_path' => 'smtpAuthMethod',
            ])
            ->add('smtp_host', TextType::class, [
                'required' => false,
                'property_path' => 'smtpHost',
            ])
            ->add('smtp_port', TextType::class, [
                'required' => false,
                'property_path' => 'smtpPort',
            ])
            ->add('smtp_username', TextType::class, [
                'required' => false,
                'property_path' => 'smtpUsername',
            ])
            ->add('smtp_password', TextType::class, [
                'required' => false,
                'property_path' => 'smtpPassword',
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
            'data_class' => EmailPassword::class,
        ]);
    }
}
