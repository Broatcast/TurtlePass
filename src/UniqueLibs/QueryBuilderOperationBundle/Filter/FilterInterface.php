<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use UniqueLibs\QueryBuilderOperationBundle\Container\FilteredMapper;

interface FilterInterface
{
    /**
     * @param FilteredMapper $filteredMapper
     * @param Request        $request
     * @param QueryBuilder   $queryBuilder
     *
     * @return mixed
     */
    public function executeRequest(FilteredMapper $filteredMapper, Request $request, QueryBuilder $queryBuilder);

    /**
     * @param FilteredMapper $filteredMapper
     * @param QueryBuilder   $queryBuilder
     * @param string         $string
     *
     * @return mixed
     */
    public function executeString(FilteredMapper $filteredMapper, QueryBuilder $queryBuilder, $string);

    /**
     * @return string
     */
    public function getName();
}
