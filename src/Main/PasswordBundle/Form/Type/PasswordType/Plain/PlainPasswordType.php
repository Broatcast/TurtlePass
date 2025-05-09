<?php

namespace Main\PasswordBundle\Form\Type\PasswordType\Plain;

use Main\PasswordBundle\Form\Type\PasswordType\AbstractPasswordType;
use Main\PasswordBundle\Entity\Password;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class PlainPasswordType extends AbstractPasswordType
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
            ->add('password', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
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
            'data_class' => Password::class,
        ]);
    }
}
