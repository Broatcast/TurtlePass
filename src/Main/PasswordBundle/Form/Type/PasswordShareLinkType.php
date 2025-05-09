<?php

namespace Main\PasswordBundle\Form\Type;

use Main\PasswordBundle\Entity\PasswordShareLink;
use Main\PasswordBundle\Model\PasswordShareLinkModel;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;

class PasswordShareLinkType extends ApiAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mode', ChoiceType::class, [
                'required' => true,
                'choices_as_values' => true,
                'choices' => array(
                    'read' => PasswordShareLink::MODE_READ,
                    'read_write' => PasswordShareLink::MODE_READ_WRITE,
                ),
            ])
            ->add('valid_to', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',
                'property_path' => 'validTo',
                'format' => 'yyyy-MM-dd HH:mm',
            ])
            ->add('recipient', EmailType::class, [
                'required' => false,
            ])
            ->add('view_limit', NumberType::class, [
                'required' => false,
                'property_path' => 'viewLimit',
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
            'data_class' => PasswordShareLinkModel::class,
        ]);
    }
}