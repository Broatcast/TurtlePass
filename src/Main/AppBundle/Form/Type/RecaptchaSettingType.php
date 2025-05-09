<?php

namespace Main\AppBundle\Form\Type;

use Main\AppBundle\Entity\Setting;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Main\AppBundle\Services\SettingManager;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RecaptchaSettingType extends ApiAbstractType
{
    /**
     * @var SettingManager
     */
    protected $settingManager;

    /**
     * @param SettingManager $settingManager
     */
    public function __construct(SettingManager $settingManager)
    {
        $this->settingManager = $settingManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $private = $this->settingManager->getSetting(Setting::ID_RECAPTCHA_PRIVATE_KEY);
        $site = $this->settingManager->getSetting(Setting::ID_RECAPTCHA_SITE_KEY);

        $constraints = [new Assert\Length(['min' => 40, 'max' => 40])];

        if ($options['recaptcha_required']) {
            $constraints[] = new Assert\NotBlank();
        }

        $builder
            ->add(Setting::ID_RECAPTCHA_PRIVATE_KEY, TextType::class, [
                'required' => $options['recaptcha_required'],
                'description' => $private->getDescription(),
                'constraints' => $constraints,
            ])
            ->add(Setting::ID_RECAPTCHA_SITE_KEY, TextType::class, [
                'required' => $options['recaptcha_required'],
                'description' => $site->getDescription(),
                'constraints' => $constraints,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'recaptcha_required' => false,
        ));
    }
}
