<?php

namespace Main\UserBundle\Form\Type;

use Main\UserBundle\Entity\User;
use Main\UserBundle\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

abstract class AbstractUserType extends ApiAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', TextType::class, [
                'required' => true,
                'property_path' => 'firstName',
                'translation_domain' => false,
                'label_attr' => ['translate' => 'USER.FIRST_NAME'],
            ])
            ->add('last_name', TextType::class, [
                'required' => true,
                'property_path' => 'lastName',
                'translation_domain' => false,
                'label_attr' => ['translate' => 'USER.LAST_NAME'],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'translation_domain' => false,
                'label_attr' => ['translate' => 'USER.EMAIL'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length(['max' => 255]),
                ],
            ]);

        if (!$options['own_user']) {
            $builder
                ->add('admin', ChoiceType::class, [
                    'required' => false,
                    'mapped' => false,
                    'choices' => [
                        'No' => 0,
                        'Yes' => 1,
                    ],
                    'choices_as_values' => true,
                ]);
        }

        $builder
            ->add('user_groups', EntityType::class, [
                'class' => UserGroup::class,
                'property_path' => 'userGroups',
                'required' => false,
                'multiple' => true,
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
            'data_class' => User::class,
            'own_user' => false,
            'validation_groups' => [
                'Default',
                'EditUser',
            ],
        ]);
    }
}
