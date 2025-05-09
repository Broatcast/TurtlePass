<?php

namespace Main\PasswordBundle\Form\Type\PasswordType;

use Main\PasswordBundle\Form\Type\CustomFieldType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractPasswordType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('notice', TextareaType::class, [
                'required' => false,
            ])
            ->add('custom_fields', CollectionType::class, [
                'required' => false,
                'property_path' => 'customFields',
                'entry_type' => CustomFieldType::class,
                'allow_add' => $options['allow_add_custom_fields'],
                'allow_delete' => $options['allow_delete_custom_fields'],
                'by_reference' => false,
            ])
        ;

        if ($options['log_enabled_available'] !== false) {
            $builder
                ->add('log_enabled', ChoiceType::class, [
                    'required' => true,
                    'property_path' => 'logEnabled',
                    'choices' => [0, 1],
                    'choices_as_values' => true,
                    'choice_value' => function ($value) {
                        return $value;
                    },
                ])
            ;
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'log_enabled_available' => true,
            'allow_add_custom_fields' => true,
            'allow_delete_custom_fields' => true,
        ));
    }
}
