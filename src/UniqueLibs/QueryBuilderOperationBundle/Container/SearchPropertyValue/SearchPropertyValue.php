<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container\SearchPropertyValue;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;
use UniqueLibs\QueryBuilderOperationBundle\Exception\UnexpectedException;
use UniqueLibs\QueryBuilderOperationBundle\SearchPropertyValue\SearchPropertyValueInterface;

class SearchPropertyValue implements SearchPropertyValueInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->validateValue($value);

        $this->value = $value;
    }

    /**
     * @param string $value
     *
     * @return string
     *
     * @throws InvalidSearchFilterSyntaxException
     * @throws UnexpectedException
     */
    public function validateValue($value)
    {
        if (!is_string($value) || !strlen($value)) {
            throw new UnexpectedException('Given value needs to be a string.');
        }

        if (strlen($value) > 256) {
            throw new InvalidSearchFilterSyntaxException(sprintf("The value '%s...' must contain less then 64 characters.", addslashes(substr($value, 0, 256))));
        }
    }
}
