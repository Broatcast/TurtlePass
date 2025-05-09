<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators;

use Doctrine\ORM\QueryBuilder;
use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;

class NotEqualOperator extends AbstractSearchOperator
{
    const OPERATOR = '!=';

    public function __construct()
    {
        $this->operator = self::OPERATOR;
        $this->isArrayOperator = false;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $property
     * @param mixed        $data
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function execute(QueryBuilder $queryBuilder, $property, $data)
    {
        $parameterId = $this->generateUniqueParameterId();

        $queryBuilder->setParameter($parameterId, $data);

        return $queryBuilder->expr()->neq($property, ':' . $parameterId);
    }
}
