<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\QueryFunction;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;

interface QueryFunctionInterface
{
    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return string|array
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function execute($name, array $parameters);
}
