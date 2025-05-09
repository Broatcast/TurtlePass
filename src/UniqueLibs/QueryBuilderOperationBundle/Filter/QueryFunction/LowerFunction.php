<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\QueryFunction;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;

class LowerFunction extends AbstractQueryFunction
{
    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function execute($name, array $parameters)
    {
        if (count($parameters) != 1) {
            throw new InvalidSearchFilterSyntaxException(sprintf('Invalid number of arguments of function %s', $name));
        }

        return strtolower($parameters[0]);
    }
}
