<?php

namespace Main\PasswordBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class AddPasswordUserGroupAccessType extends EditPasswordUserGroupAccessType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_group', EntityType::class, [
                'required' => true,
                'property_path' => 'userGroup',
                'class' => 'Main\UserBundle\Entity\UserGroup',
            ])
        ;

        parent::buildForm($builder, $options);
    }
}
