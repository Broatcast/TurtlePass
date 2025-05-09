<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes;

abstract class AbstractAttributeType implements AttributeTypeInterface
{
    /**
     * @return array
     */
    abstract public function getAllowedOperators();

    /**
     * @param string $input
     *
     * @return mixed
     */
    public function getDataByInput($input)
    {
        return $input;
    }
}
