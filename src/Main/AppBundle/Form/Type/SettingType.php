<?php

namespace Main\AppBundle\Form\Type;

use Main\AppBundle\Entity\Setting;
use UniqueLibs\ApiBundle\Form\ApiAbstractType;
use Main\AppBundle\Services\SettingManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingType extends ApiAbstractType
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
        /** @var Setting[] $settings */
        $settings = $this->settingManager->qbAllSettings()->getQuery()->getResult();

        foreach ($settings as $setting) {
            if (in_array($setting->getId(), Setting::getRecaptchaIds())) {
                continue;
            }

            if ($setting->getSettingType() == Setting::TYPE_TEXT) {
                $builder
                    ->add($setting->getId(), TextType::class, [
                        'required' => false,
                        'description' => $setting->getDescription(),
                        'constraints' => [
                            new Assert\Length(['max' => 255]),
                        ],
                    ])
                ;
            } else if ($setting->getSettingType() == Setting::TYPE_BOOLEAN) {
                $builder
                    ->add($setting->getId(), ChoiceType::class, [
                        'required' => false,
                        'description' => $setting->getDescription(),
                        'choices' => [0, 1],
                        'choices_as_values' => true,
                        'choice_value' => function ($value) {
                            return $value;
                        },
                    ])
                ;
            } else if ($setting->getSettingType() == Setting::TYPE_CHOICE) {
                $builder
                    ->add($setting->getId(), ChoiceType::class, [
                        'required' => false,
                        'choices_as_values' => true,
                        'choices' => $setting->getChoices(),
                        'description' => $setting->getDescription(),
                    ])
                ;
            }
        }
    }
}
