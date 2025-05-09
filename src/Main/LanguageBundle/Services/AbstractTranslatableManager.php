<?php

namespace Main\LanguageBundle\Services;

use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AbstractTranslatableManager
{
    /** @var string */
    protected $locale;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->locale = 'en';

        if ($requestStack->getCurrentRequest() instanceof Request) {
            $this->locale = $requestStack->getCurrentRequest()->getLocale();
        }
    }

    /**
     * @param Query $query
     *
     * @return Query
     */
    public function getTranslatedQuery(Query $query)
    {
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $this->locale);

        return $query;
    }
}
