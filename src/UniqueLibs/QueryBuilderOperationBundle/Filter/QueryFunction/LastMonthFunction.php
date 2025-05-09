<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\QueryFunction;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;

class LastMonthFunction extends AbstractDateFunction
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
        if (count($parameters) != 0) {
            throw new InvalidSearchFilterSyntaxException(sprintf('Invalid number of arguments of function %s', $name));
        }

        return parent::execute($name, array('last month 00:00:00'));
    }
}
