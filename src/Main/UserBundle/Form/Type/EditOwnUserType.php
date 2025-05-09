<?php

namespace Main\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class EditOwnUserType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('admin')
            ->remove('user_groups')
        ;
    }
}
