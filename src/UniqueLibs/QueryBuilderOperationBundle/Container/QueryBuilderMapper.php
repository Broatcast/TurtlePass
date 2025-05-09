<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container;

class QueryBuilderMapper extends EntityMapper
{
    /** @var EntityMapper[] */
    protected $entityMappers;

    public function __construct()
    {
        parent::__construct();

        $this->entityMappers = array();
    }

    /**
     * @param string       $property
     * @param EntityMapper $entityMapper
     * @param null|string  $alias
     *
     * @return $this
     */
    public function addMapper($property, EntityMapper $entityMapper, $alias = null)
    {
        $this->entityMappers[$property] = array($entityMapper, $alias);

        return $this;
    }

    /**
     * @return EntityMapper[]
     */
    public function getEntityMappers()
    {
        return $this->entityMappers;
    }
}
