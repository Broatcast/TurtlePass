<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container;

use UniqueLibs\QueryBuilderOperationBundle\Filter\QueryFunction\QueryFunctionInterface;
use UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators\SearchOperatorInterface;

class SearchFilterConfiguration
{
    /**
     * @var SearchOperatorInterface[]
     */
    protected $searchOperators;

    /**
     * @var QueryFunctionInterface[]
     */
    protected $queryFunctions;

    public function __construct()
    {
        $this->searchOperators = array();
        $this->queryFunctions = array();
    }

    /**
     * @param SearchOperatorInterface $searchOperator
     *
     * @return $this
     */
    public function addSearchOperator(SearchOperatorInterface $searchOperator)
    {
        $this->searchOperators[$searchOperator->getOperator()] = $searchOperator;

        return $this;
    }

    /**
     * @return SearchOperatorInterface[]
     */
    public function getSearchOperators()
    {
        return $this->searchOperators;
    }

    /**
     * @param string                 $name
     * @param QueryFunctionInterface $queryFunction
     *
     * @return $this
     */
    public function addQueryFunction($name, QueryFunctionInterface $queryFunction)
    {
        $this->queryFunctions[strtoupper($name)] = $queryFunction;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return null|QueryFunctionInterface
     */
    public function getQueryFunction($name)
    {
        $name = strtoupper($name);

        if (array_key_exists($name, $this->queryFunctions)) {
            return $this->queryFunctions[$name];
        }

        return null;
    }
}
