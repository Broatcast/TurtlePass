<?php

namespace Main\PasswordBundle\Form\Type;

use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Main\PasswordBundle\Entity\PasswordGroupAccess;
use Main\PasswordBundle\Model\AccessRightModel;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPasswordGroupAccessType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('right', ChoiceType::class, [
                'required' => true,
                'choices' => AccessRightModel::getRights(),
                'choices_as_values' => true,
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
            'data_class' => PasswordGroupAccess::class,
        ]);
    }
}
