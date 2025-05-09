<?php

namespace Main\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('backup', CheckboxType::class, array(
                'label' => 'I have created a backup of my database.',
                'required' => true,
                'constraints' => array(
                ),
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'allow_extra_fields' => true,
            'cascade_validation' => true,
        ));
    }

    /**
     * @return null
     */
    public function getName()
    {
        return null;
    }

}