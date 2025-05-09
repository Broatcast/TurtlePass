<?php

namespace Main\PasswordBundle\Form\Type;

use Main\PasswordBundle\Entity\Password;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MovePasswordType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password_group', EntityType::class, [
                'class' => 'Main\PasswordBundle\Entity\PasswordGroup',
                'required' => true,
                'mapped' => false,
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
