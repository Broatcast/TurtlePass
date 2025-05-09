<?php

namespace Main\PasswordBundle\Form\Type\PasswordType\SoftwareLicense;

use Main\PasswordBundle\Entity\PasswordType\SoftwareLicensePassword;
use Main\PasswordBundle\Form\Type\PasswordType\AbstractPasswordType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SoftwareLicensePasswordType extends AbstractPasswordType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('url', UrlType::class, [
                'required' => false,
            ])
            ->add('version', TextType::class, [
                'required' => false,
            ])
            ->add('license_key', TextType::class, [
                'required' => false,
                'property_path' => 'licenseKey',
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
            'data_class' => SoftwareLicensePassword::class,
        ]);
    }
}
