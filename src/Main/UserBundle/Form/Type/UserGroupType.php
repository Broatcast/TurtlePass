<?php

namespace Main\UserBundle\Form\Type;

use Main\UserBundle\Entity\UserGroup;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;

class UserGroupType extends ApiAbstractType
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
                'translation_domain' => false,
                'label_attr' => ['translate' => 'GROUP.NAME'],
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
            'data_class' => UserGroup::class,
        ]);
    }
}
