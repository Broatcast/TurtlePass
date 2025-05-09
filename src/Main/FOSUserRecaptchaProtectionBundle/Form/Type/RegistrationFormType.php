<?php

namespace Main\FOSUserRecaptchaProtectionBundle\Form\Type;

use Main\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;

class RegistrationFormType extends BaseRegistrationFormType
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'my_fos_user_registration';
    }
}
