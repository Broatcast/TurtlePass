<?php

namespace Main\UserBundle\Form\Type;

use Main\ApiBundle\Form\Type\Api\TokenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserTokenType extends TokenType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('unique_key', TextType::class, [
                'required' => false,
            ])
            ->add('visible', ChoiceType::class, [
                'choices' => ['0' => 'No', '1' => 'Yes'],
                'required' => false,
            ])
        ;
    }
}
