<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators;

use Doctrine\ORM\QueryBuilder;
use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;

class InOperator extends AbstractSearchOperator
{
    const OPERATOR = 'IN';

    public function __construct()
    {
        $this->operator = self::OPERATOR;
        $this->isArrayOperator = true;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $property
     * @param mixed        $data
     *
     * @return \Doctrine\ORM\Query\Expr\Func
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function execute(QueryBuilder $queryBuilder, $property, $data)
    {
        $parameterId = $this->generateUniqueParameterId();

        $queryBuilder->setParameter($parameterId, $data);

        return $queryBuilder->expr()->in($property, ':' . $parameterId);
    }
}
