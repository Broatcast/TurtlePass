<?php

namespace Main\AppBundle\Twig;

use Main\AppBundle\Entity\Setting;
use Main\AppBundle\Services\SettingManager;

class PageTitleExtension extends \Twig_Extension
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
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_page_title', [$this, 'getPageTitle']),
        ];
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->settingManager->getSetting(Setting::ID_PAGE_TITLE)->getValue();
    }
}
