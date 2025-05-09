<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\QueryFunction;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;

abstract class AbstractQueryFunction implements QueryFunctionInterface
{
    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return string|array
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function execute($name, array $parameters)
    {
        return '0';
    }
}
