<?php

namespace Main\UserBundle\Form\Type;

use Main\UserBundle\Entity\LoginAccess;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;

class LoginAccessType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from_ip', TextType::class, [
                'required' => true,
                'mapped' => false,
                'translation_domain' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Ip(),
                ],
            ])
            ->add('to_ip', TextType::class, [
                'required' => true,
                'mapped' => false,
                'translation_domain' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Ip(),
                ],
            ])
            ->add('whitelist', ChoiceType::class, [
                'required' => false,
                'translation_domain' => false,
                'choice_translation_domain' => false,
                'choices' => [0, 1],
                'choices_as_values' => true,
                'choice_value' => function ($value) {
                    return $value;
                },
                'constraints' => [
                    new Assert\NotBlank(),
                ]
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
            'data_class' => LoginAccess::class,
        ]);
    }
}
