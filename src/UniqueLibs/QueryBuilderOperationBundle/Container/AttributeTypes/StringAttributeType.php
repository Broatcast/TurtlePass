<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes;

use UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators as SearchOperators;

class StringAttributeType extends AbstractAttributeType
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
            SearchOperators\InOperator::OPERATOR,
            SearchOperators\NotInOperator::OPERATOR,
        );
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function getDataByInput($input)
    {
        return trim($input, '\'"');
    }
}
