<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractSearchOperator implements SearchOperatorInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $property
     * @param mixed        $data
     *
     * @return \Doctrine\ORM\Query\Expr
     */
    abstract public function execute(QueryBuilder $queryBuilder, $property, $data);

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var bool
     */
    protected $isArrayOperator;

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return bool
     */
    public function isArrayOperator()
    {
        return $this->isArrayOperator;
    }

    /**
     * @return string
     */
    public function generateUniqueParameterId()
    {
        return uniqid('uniquelibs_search_filter_');
    }
}
