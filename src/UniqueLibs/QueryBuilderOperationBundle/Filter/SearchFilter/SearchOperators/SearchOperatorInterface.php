<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators;

use Doctrine\ORM\QueryBuilder;

interface SearchOperatorInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $property
     * @param mixed        $data
     *
     * @return \Doctrine\ORM\Query\Expr
     */
    public function execute(QueryBuilder $queryBuilder, $property, $data);

    /**
     * @return string
     */
    public function getOperator();

    /**
     * @return bool
     */
    public function isArrayOperator();

    /**
     * @return string
     */
    public function generateUniqueParameterId();
}
