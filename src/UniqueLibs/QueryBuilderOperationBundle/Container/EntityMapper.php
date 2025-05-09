<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container;

use UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes\AttributeTypeInterface;

class EntityMapper
{
    /** @var array[] */
    protected $entityAttributes;

    public function __construct()
    {
        $this->entityAttributes = array();
    }

    /**
     * @param string                 $property
     * @param AttributeTypeInterface $attributeType
     * @param array                  $filters
     * @param string|null            $alias
     *
     * @return $this
     */
    public function add($property, AttributeTypeInterface $attributeType, array $filters, $alias = null)
    {
        $this->entityAttributes[$property] = array($attributeType, $filters, $alias);

        return $this;
    }

    /**
     * @return array[]
     */
    public function getEntityAttributes()
    {
        return $this->entityAttributes;
    }
}
