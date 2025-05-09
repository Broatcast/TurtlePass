<?php

namespace Main\PasswordBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class AddPasswordGroupAccessType extends EditPasswordGroupAccessType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'required' => true,
                'class' => 'Main\UserBundle\Entity\User',
            ])
        ;

        parent::buildForm($builder, $options);
    }
}
