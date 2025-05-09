<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use UniqueLibs\QueryBuilderOperationBundle\Container\FilteredMapper;

class OrderFilter extends AbstractFilter
{
    /**
     * @param FilteredMapper $filteredMapper
     * @param Request        $request
     * @param QueryBuilder   $queryBuilder
     *
     * @return QueryBuilder
     */
    public function executeRequest(FilteredMapper $filteredMapper, Request $request, QueryBuilder $queryBuilder)
    {
        $filteredMapping = $filteredMapper->getAttributes();

        $filteredProperties = array();

        $sort = $request->get('sort');
        $orders = array();

        if (!is_null($sort) && !is_array($sort)) {
            $elements = explode(',', $sort, 99);

            foreach ($elements as $element) {
                if ($element) {
                    $sort = 'asc';

                    if (substr($element, 0, 1) == '+') {
                        $element = substr($element, 1);
                    } else if (substr($element, 0, 1) == '-') {
                        $element = substr($element, 1);
                        $sort = 'desc';
                    }

                    if (array_key_exists($element, $filteredMapping)) {
                        $filteredProperties[] = $element;
                        $orders[$filteredMapping[$element][0]] = $sort;
                    }
                }
            }
        }

        $this->clearAndAddOrder($queryBuilder, $orders);

        return $queryBuilder;
    }

    /**
     * @param FilteredMapper $filteredMapper
     * @param QueryBuilder   $queryBuilder
     * @param string         $string
     *
     * @return QueryBuilder
     */
    public function executeString(FilteredMapper $filteredMapper, QueryBuilder $queryBuilder, $string)
    {
        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $orderElements
     */
    protected function clearAndAddOrder(QueryBuilder $queryBuilder, array $orderElements)
    {
        if (count($orderElements) > 0) {
            $first = true;

            foreach ($orderElements as $key => $sort) {
                if ($first) {
                    $queryBuilder->orderBy($key, $sort);
                } else {
                    $queryBuilder->addOrderBy($key, $sort);
                }
            }
        }
    }
}
