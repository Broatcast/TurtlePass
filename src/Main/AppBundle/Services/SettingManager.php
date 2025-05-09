<?php

namespace Main\AppBundle\Services;

use Main\AppBundle\Entity\Setting;
use Main\AppBundle\Entity\SettingRepository;
use Main\AppBundle\Services\Fixtures\SettingFixtures;

class SettingManager
{
    /**
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * @var Setting[]
     */
    protected $cache;

    /**
     * @param SettingRepository $settingRepository
     */
    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
        $this->cache = [];
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllSettings()
    {
        return $this->settingRepository->qbAll();
    }

    /**
     * @param Setting $setting
     */
    public function updateSetting(Setting $setting)
    {
        $this->settingRepository->save($setting);
    }

    /**
     * @param $id
     *
     * @return Setting
     */
    public function getSetting($id)
    {
        if (array_key_exists($id, $this->cache)) {
            return $this->cache[$id];
        }

        $setting = $this->settingRepository->find($id);

        if ($setting instanceof Setting) {
            $this->cache[$id] = $setting;

            return $setting;
        }

        $this->syncSettings();

        if (array_key_exists($id, $this->cache)) {
            return $this->cache[$id];
        }

        throw new \LogicException('Please add the setting to the setting fixtures.');
    }

    public function syncSettings()
    {
        $settingFixtures = SettingFixtures::getSettingFixtures();

        foreach ($settingFixtures as $settingFixture) {
            if (array_key_exists($settingFixture->getId(), $this->cache)) {
                continue;
            }

            $setting = $this->settingRepository->find($settingFixture->getId());

            if ($setting instanceof Setting) {
                $this->cache[$settingFixture->getId()] = $setting;
            } else {
                $this->settingRepository->save($settingFixture);
                $this->cache[$settingFixture->getId()] = $settingFixture;
            }
        }
    }
}
