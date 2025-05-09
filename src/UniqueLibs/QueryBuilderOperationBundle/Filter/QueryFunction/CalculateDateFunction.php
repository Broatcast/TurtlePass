<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter\QueryFunction;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;

class CalculateDateFunction extends AbstractQueryFunction
{
    /**
     * @param string $name       Format: CALCULATE_DATE(PnYnMnDnHnInS[, YYYY-MM-DD HH:ii:ss[, true|false]])
     * @param array  $parameters
     *
     * @return string
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function execute($name, array $parameters)
    {
        if (count($parameters) != 1 && count($parameters) != 2 && count($parameters) != 3) {
            throw new InvalidSearchFilterSyntaxException(sprintf('Invalid number of arguments of function %s', $name));
        }

        if (!preg_match('/^[P]([0-9]+[Y|M|D|H|I|S])+$/gi', $parameters[0], $matches)) {
            throw new InvalidSearchFilterSyntaxException(sprintf('Invalid format for interval in function %s. (Valid format: PnYnMnDnHnInS)', $name));
        }

        $dateInterval = new \DateInterval($parameters[0]);

        if (isset($parameters[2]) && ($parameters[2] == 'true' || $parameters[2] == '1')) {
            $dateInterval->invert = 1;
        }

        if (isset($parameters[1]) && !preg_match('/^([0-9]{4}\-[0-9]{2}-[0-9]{2})\s*([0-9]{2}:[0-9]{2}:[0-9]{2})?$/gi', $parameters[1])) {
            throw new InvalidSearchFilterSyntaxException(sprintf('Invalid format for date in function %s. (Valid format: YYYY-MM-DD HH:ii:ss|YYYY-MM-DD)', $name));
        } else if (isset($parameters[1])) {
            $now = new \DateTime($parameters[1]);
        } else {
            $now = new \DateTime();
        }

        return $now->add($dateInterval)->format('Y-m-d H:i:s');
    }
}
