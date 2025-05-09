<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;
use UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators as SearchOperators;

class NumberAttributeType extends AbstractAttributeType
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
            SearchOperators\BitwiseAndOperator::OPERATOR,
        );
    }

    /**
     * @param string $input
     *
     * @return int
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function getDataByInput($input)
    {
        $input = str_replace("'", '', $input);

        if (!is_numeric(str_replace('%', '', $input))) {
            throw new InvalidSearchFilterSyntaxException(sprintf("Given value '%s' needs to be a number.", str_replace("'", '', $input)));
        }

        return $input;
    }
}
