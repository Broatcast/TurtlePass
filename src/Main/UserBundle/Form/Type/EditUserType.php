<?php

namespace Main\UserBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class EditUserType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'translation_domain' => false,
                'label_attr' => ['translate' => 'USER.NAME'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 3, 'max' => 32]),
                ],
            ]);

        if (!$options['own_user']) {
            $builder
                ->add('password', PasswordType::class, [
                    'required' => false,
                    'mapped' => false,
                    'translation_domain' => false,
                    'label_attr' => ['translate' => 'USER.PASSWORD'],
                    'constraints' => [
                        new Assert\Length(['min' => 5, 'max' => 64]),
                    ],
                ])
            ;
        }

        parent::buildForm($builder, $options);
    }
}
