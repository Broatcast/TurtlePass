<?php

namespace Main\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class AddUserType extends AbstractUserType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
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
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'mapped' => false,
                'translation_domain' => false,
                'label_attr' => ['translate' => 'USER.PASSWORD'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 5, 'max' => 64]),
                ],
            ])
        ;

        parent::buildForm($builder, $options);
    }
}
