<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;
use UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators as SearchOperators;

class DateTimeAttributeType extends AbstractAttributeType
{
    /**
     * @return array
     */
    public function getAllowedOperators()
    {
        return array(
            SearchOperators\EqualOperator::OPERATOR,
            SearchOperators\NotEqualOperator::OPERATOR,
            SearchOperators\LikeOperator::OPERATOR,
            SearchOperators\NotLikeOperator::OPERATOR,
            SearchOperators\GreaterThanOperator::OPERATOR,
            SearchOperators\GreaterThanEqualOperator::OPERATOR,
            SearchOperators\LowerThanOperator::OPERATOR,
            SearchOperators\LowerThanEqualOperator::OPERATOR,
            SearchOperators\InOperator::OPERATOR,
            SearchOperators\NotInOperator::OPERATOR,
        );
    }

    /**
     * @param string $input
     *
     * @return \DateTime
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function getDataByInput($input)
    {
        if (!preg_match('/^([0-9]{4}\-[0-9]{2}-[0-9]{2})\s*([0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $input)) {
            throw new InvalidSearchFilterSyntaxException('Invalid format for date (Format must match: YYYY-MM-DD HH:mm:ss|YYYY-MM-DD)');
        }

        return new \DateTime($input);
    }
}
