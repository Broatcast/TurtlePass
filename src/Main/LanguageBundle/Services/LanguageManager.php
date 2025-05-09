<?php

namespace Main\LanguageBundle\Services;

use Doctrine\ORM\QueryBuilder;
use Main\LanguageBundle\Entity\Language;
use Main\LanguageBundle\Entity\LanguageRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class LanguageManager extends AbstractTranslatableManager
{
    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    /**
     * @param RequestStack       $requestStack
     * @param LanguageRepository $languageRepository
     */
    public function __construct(RequestStack $requestStack, LanguageRepository $languageRepository)
    {
        parent::__construct($requestStack);

        $this->languageRepository = $languageRepository;
    }

    /**
     * @return QueryBuilder
     */
    public function qbAllLanguages()
    {
        return $this->languageRepository->createQueryBuilder('language');
    }

    /**
     * @return null|Language
     */
    public function getDefaultLanguage()
    {
        return $this->languageRepository->findOneBy(['id' => 'en']);
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return \Doctrine\ORM\Query
     */
    public function translateQueryBuilder(QueryBuilder $queryBuilder)
    {
        return $this->getTranslatedQuery($queryBuilder->getQuery());
    }

    /**
     * @return Language[]|array
     */
    public function getAllLanguages()
    {
        return $this->languageRepository->findAll();
    }

}
