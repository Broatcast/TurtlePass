<?php

namespace Main\LanguageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Main\LanguageBundle\Entity\LanguageRepository")
 * @ORM\Table(name="languages")
 *
 * @Gedmo\TranslationEntity(class="Main\LanguageBundle\Entity\LanguageTranslation")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Language
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=2, options={"fixed"=true})
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Gedmo\Translatable
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Main\LanguageBundle\Entity\LanguageTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     *
     * @var ArrayCollection|LanguageTranslation[]
     */
    private $translations;

    /**
     * @param null|string $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;

        $this->translations = new ArrayCollection();
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection|LanguageTranslation
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param LanguageTranslation $t
     */
    public function addTranslation(LanguageTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }
}
