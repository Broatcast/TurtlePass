<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes;

interface AttributeTypeInterface
{
    /**
     * @return array
     */
    public function getAllowedOperators();

    /**
     * @param string $input
     *
     * @return mixed
     */
    public function getDataByInput($input);
}
