<?php

namespace Main\LanguageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="Main\LanguageBundle\Entity\LanguageTranslationRepository")
 * @ORM\Table(name="language_translations", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="language_translations_uqe", columns={"locale", "object_id", "field"})
 * })
 *
 * @Serializer\ExclusionPolicy("all")
 */
class LanguageTranslation extends AbstractPersonalTranslation
{
    /**
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    /**
     * @ORM\ManyToOne(targetEntity="Main\LanguageBundle\Entity\Language", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Language
     */
    protected $object;

    /**
     * Get related object
     *
     * @return Language
     */
    public function getObject()
    {
        return $this->object;
    }
}
