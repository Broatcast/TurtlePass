<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container;

use UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes\AttributeTypeInterface;
use UniqueLibs\QueryBuilderOperationBundle\Helper\StringHelper;

class FilteredMapper
{
    /** @var array[] */
    protected $attributes;

    public function __construct()
    {
        $this->attributes = array();
    }

    /**
     * @param string                 $attribute
     * @param AttributeTypeInterface $attributeType
     * @param string                 $alias
     *
     * @return $this
     */
    public function add($attribute, AttributeTypeInterface $attributeType, $alias)
    {
        $this->attributes[StringHelper::snake($alias)] = array($attribute, $attributeType);

        return $this;
    }

    /**
     * @return array[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
