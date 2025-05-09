<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use UniqueLibs\QueryBuilderOperationBundle\Container\EntityMapper;
use UniqueLibs\QueryBuilderOperationBundle\Container\FilteredMapper;
use UniqueLibs\QueryBuilderOperationBundle\Container\QueryBuilderMapper;
use UniqueLibs\QueryBuilderOperationBundle\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;

class FilterManager
{
    /** @var FilterInterface[] */
    protected $filters;

    public function __construct()
    {
        $this->filters = array();
    }

    /**
     * @param Request            $request
     * @param QueryBuilder       $queryBuilder
     * @param QueryBuilderMapper $queryBuilderMapper
     *
     * @return QueryBuilder|mixed
     */
    public function executeRequest(Request $request, QueryBuilder $queryBuilder, QueryBuilderMapper $queryBuilderMapper)
    {
        foreach ($this->filters as $filterName => $filter) {
            $queryBuilder = $filter->executeRequest(
                $this->getFilteredMapperByQueryBuilderMapperAndFilter($queryBuilderMapper, $filter),
                $request,
                $queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder       $queryBuilder
     * @param QueryBuilderMapper $queryBuilderMapper
     * @param string             $string
     *
     * @return QueryBuilder|mixed
     */
    public function executeString(QueryBuilder $queryBuilder, QueryBuilderMapper $queryBuilderMapper, $string)
    {
        foreach ($this->filters as $filterName => $filter) {
            $queryBuilder = $filter->executeString(
                $this->getFilteredMapperByQueryBuilderMapperAndFilter($queryBuilderMapper, $filter),
                $queryBuilder,
                $string);
        }

        return $queryBuilder;
    }

    /**
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * @param QueryBuilderMapper $queryBuilderMapper
     * @param FilterInterface    $filter
     *
     * @return FilteredMapper
     */
    protected function getFilteredMapperByQueryBuilderMapperAndFilter(QueryBuilderMapper $queryBuilderMapper, FilterInterface $filter)
    {
        $filteredMapper = new FilteredMapper();

        foreach ($queryBuilderMapper->getEntityMappers() as $queryBuilderAttribute => $queryBuilderAttributeData) {
            /** @var EntityMapper $entityMapper */
            $entityMapper = $queryBuilderAttributeData[0];
            $alias = $queryBuilderAttributeData[1];

            foreach ($entityMapper->getEntityAttributes() as $entityAttribute => $entityAttributeData) {
                if (in_array($filter->getName(), $entityAttributeData[1])) {
                    if (is_null($alias)) {
                        $filteredMapper->add($queryBuilderAttribute . '.' . $entityAttribute, $entityAttributeData[0], $queryBuilderAttribute . '.' . $entityAttribute);
                    } else {
                        if ($alias == '.') {
                            $filteredMapper->add($queryBuilderAttribute . '.' . $entityAttribute, $entityAttributeData[0], $entityAttribute);
                            $filteredMapper->add($queryBuilderAttribute . '.' . $entityAttribute, $entityAttributeData[0], $queryBuilderAttribute . '.' . $entityAttribute);
                        } else {
                            $filteredMapper->add($queryBuilderAttribute . '.' . $entityAttribute, $entityAttributeData[0], $alias . '.' . $entityAttribute);
                        }
                    }
                }
            }
        }

        foreach ($queryBuilderMapper->getEntityAttributes() as $entityAttribute => $entityAttributeData) {
            if (in_array($filter->getName(), $entityAttributeData[1])) {
                if (is_null($entityAttributeData[2])) {
                    $mapperAlias = $entityAttribute;
                } else {
                    $mapperAlias = $entityAttributeData[2];
                }

                $filteredMapper->add($entityAttribute, $entityAttributeData[0], $mapperAlias);
            }
        }

        return $filteredMapper;
    }
}
