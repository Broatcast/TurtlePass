<?php

namespace Main\PasswordBundle\Form\Type;

use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Main\PasswordBundle\Entity\PasswordAccess;
use Main\PasswordBundle\Model\AccessRightModel;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPasswordAccessType extends ApiAbstractType
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
            'data_class' => PasswordAccess::class,
        ]);
    }
}
