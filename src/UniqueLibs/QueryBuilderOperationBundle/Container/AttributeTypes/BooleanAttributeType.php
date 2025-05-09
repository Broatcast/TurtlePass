<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes;

use UniqueLibs\QueryBuilderOperationBundle\Filter\SearchFilter\SearchOperators as SearchOperators;

class BooleanAttributeType extends AbstractAttributeType
{
    /**
     * @return array
     */
    public function getAllowedOperators()
    {
        return array(
            SearchOperators\EqualOperator::OPERATOR,
            SearchOperators\NotEqualOperator::OPERATOR,
        );
    }
}
