<?php

namespace Main\AppBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Main\AppBundle\Entity\SettingRepository")
 * @ORM\Table(name="settings")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Setting
{
    const ID_PAGE_TITLE = 'PAGE_TITLE';
    const ID_ENABLE_REGISTRATION = 'ENABLE_REGISTRATION';
    const ID_ENABLE_CONFIRMATION_EMAIL = 'ENABLE_CONFIRMATION_EMAIL';
    const ID_ENABLE_PASSWORD_RESET = 'ENABLE_PASSWORD_RESET';
    const ID_ONLY_WHITELISTED = 'ONLY_WHITELISTED';

    const ID_MAIL_TRANSPORT = 'MAIL_TRANSPORT';
    const ID_MAIL_USERNAME = 'MAIL_USERNAME';
    const ID_MAIL_PASSWORD = 'MAIL_PASSWORD';
    const ID_MAIL_HOST = 'MAIL_HOST';
    const ID_MAIL_PORT = 'MAIL_PORT';
    const ID_MAIL_ENCRYPTION = 'MAIL_ENCRYPTION';
    const ID_MAIL_AUTH_MODE = 'MAIL_AUTH_MODE';
    const ID_MAIL_SENDER_ADDRESS = 'MAIL_SENDER_ADDRESS';
    const ID_LDAP_USER_PROFILE_FULFILLMENT_REQUIREMENT = 'LDAP_USER_PROFILE_FULFILLMENT_REQUIREMENT';

    const ID_RECAPTCHA_SITE_KEY = 'RECAPTCHA_SITE_KEY';
    const ID_RECAPTCHA_PRIVATE_KEY = 'RECAPTCHA_PRIVATE_KEY';

    const ID_RECAPTCHA_NEEDED_FAILS = 'RECAPTCHA_NEEDED_FAILS';

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_TEXT = 'text';
    const TYPE_CHOICE = 'choice';

    /**
     * @return array
     */
    public static function getRecaptchaIds()
    {
        return [self::ID_RECAPTCHA_SITE_KEY, self::ID_RECAPTCHA_PRIVATE_KEY];
    }

    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="setting_type")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $settingType;

    /**
     * @ORM\Column(type="string")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $value;

    /**
     * @ORM\Column(type="string")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @Serializer\Expose()
     *
     * @var array
     */
    protected $choices;

    /**
     * @param null|string $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
        $this->options = [];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSettingType()
    {
        return $this->settingType;
    }

    /**
     * @param string $settingType
     */
    public function setSettingType($settingType)
    {
        $this->settingType = $settingType;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return null|string
     */
    public function getValueAllowNull()
    {
        $value = $this->getValue();

        if ($value == 'default') {
            return null;
        }

        if ($value == '') {
            return null;
        }

        return $value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param array $choices
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
    }

    /**
     * @param string $choice
     */
    public function addChoice($choice)
    {
        $this->choices[] = $choice;
    }
}
