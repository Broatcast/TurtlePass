<?php

namespace Main\AppBundle\Services\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Main\AppBundle\Entity\Setting;

final class SettingFixtures
{
    /**
     * @return ArrayCollection|Setting[]
     */
    public static function getSettingFixtures()
    {
        $settings = new ArrayCollection();

        self::add($settings, Setting::ID_PAGE_TITLE, Setting::TYPE_TEXT, 'TurtlePass - Team Password Manager', 'You can change your page title here.');
        self::add($settings, Setting::ID_ENABLE_REGISTRATION, Setting::TYPE_BOOLEAN, '1', 'You can enable and disable the user registration here.');
        self::add($settings, Setting::ID_ENABLE_PASSWORD_RESET, Setting::TYPE_BOOLEAN, '1', 'You can enable and disable the forgot password functionality.');
        self::add($settings, Setting::ID_ONLY_WHITELISTED, Setting::TYPE_BOOLEAN, '0', 'Only allow whitelisted ip addresses');

        self::add($settings, Setting::ID_MAIL_TRANSPORT, Setting::TYPE_CHOICE, 'mail', 'The exact transport method to use to deliver emails.', [
            'smtp',
            'mail',
            'sendmail',
        ]);
        self::add($settings, Setting::ID_MAIL_USERNAME, Setting::TYPE_TEXT, '', 'The username when using smtp as the transport.');
        self::add($settings, Setting::ID_MAIL_PASSWORD, Setting::TYPE_TEXT, '', 'The password when using smtp as the transport.');
        self::add($settings, Setting::ID_MAIL_HOST, Setting::TYPE_TEXT, '', 'The host to connect to when using smtp as the transport.');
        self::add($settings, Setting::ID_MAIL_PORT, Setting::TYPE_TEXT, '', 'The port when using smtp as the transport. This defaults to 465 if encryption is ssl and 25 otherwise.');
        self::add($settings, Setting::ID_MAIL_ENCRYPTION, Setting::TYPE_CHOICE, 'default', 'The encryption mode to use when using smtp as the transport.', [
            'default',
            'tls',
            'ssl'
        ]);
        self::add($settings, Setting::ID_MAIL_AUTH_MODE, Setting::TYPE_CHOICE, 'default', 'The authentication mode to use when using smtp as the transport.', [
            'default',
            'plain',
            'login',
            'cram-md5',
        ]);
        self::add($settings, Setting::ID_MAIL_SENDER_ADDRESS, Setting::TYPE_TEXT, '', 'You can enter your sender address here.');

        self::add($settings, Setting::ID_RECAPTCHA_SITE_KEY, Setting::TYPE_TEXT, '', 'Enter your recaptcha site key here. (Leave blank to disable)');
        self::add($settings, Setting::ID_RECAPTCHA_PRIVATE_KEY, Setting::TYPE_TEXT, '', 'Enter your recaptcha private key here. (Leave blank to disable)');

        self::add($settings, Setting::ID_LDAP_USER_PROFILE_FULFILLMENT_REQUIREMENT, Setting::TYPE_BOOLEAN, '1', 'You can enable and disable the user registration here.');

        self::add($settings, Setting::ID_RECAPTCHA_NEEDED_FAILS, Setting::TYPE_TEXT, '10', 'Enter the number of invalid login trials before a recaptcha will be shown.');

        return $settings;
    }

    /**
     * @param ArrayCollection $arrayCollection
     * @param string          $id
     * @param string          $type
     * @param string          $value
     * @param string          $description
     * @param array           $choices
     */
    protected static function add(ArrayCollection $arrayCollection, $id, $type, $value, $description, $choices = [])
    {
        $setting = new Setting($id);
        $setting->setSettingType($type);
        $setting->setValue($value);
        $setting->setDescription($description);
        $setting->setChoices($choices);

        $arrayCollection->add($setting);
    }
}