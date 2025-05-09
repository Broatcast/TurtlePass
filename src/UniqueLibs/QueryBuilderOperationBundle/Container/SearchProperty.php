<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Container;

use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;
use UniqueLibs\QueryBuilderOperationBundle\Exception\UnexpectedException;

class SearchProperty
{
    /**
     * @var string|null
     */
    protected $column;

    /**
     * @var array
     */
    protected $operators;

    /**
     * @var string
     */
    protected $operatorKey;

    /**
     * @var string
     */
    protected $operatorName;

    /**
     * @var array
     */
    protected $values;

    public function __construct()
    {
        $this->values = array();
        $this->operators = array();
        $this->operatorKey = '';
        $this->operatorName = '';
    }

    /**
     * @return null|string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return bool
     */
    public function hasColumn()
    {
        return $this->column !== null;
    }

    /**
     * @param string $column
     */
    public function setColumn($column)
    {
        $column = $this->validateColumn($column);

        $this->column = $column;
    }

    /**
     * @param string $column
     *
     * @return string
     *
     * @throws InvalidSearchFilterSyntaxException
     * @throws UnexpectedException
     */
    public function validateColumn($column)
    {
        if (!is_string($column) || !strlen($column)) {
            throw new UnexpectedException('Given column needs to be a string.');
        }

        if (strlen($column) > 64) {
            throw new InvalidSearchFilterSyntaxException(sprintf("The column '%s...' must contain less then 64 characters.", addslashes(substr($column, 0, 64))));
        }

        return strtolower($column);
    }

    /**
     * @return string
     */
    public function getOperatorKey()
    {
        return $this->operatorKey;
    }

    /**
     * @return string
     */
    public function getOperatorName()
    {
        return $this->operatorName;
    }

    /**
     * @return bool
     */
    public function hasOperators()
    {
        return count($this->operators) > 0;
    }

    /**
     * @param string $operator
     *
     * @throws UnexpectedException
     */
    public function addOperator($operator)
    {
        $operator = $this->validateOperator($operator);

        $this->operators[] = $operator;
        $this->operatorKey = implode('_', $this->operators);
        $this->operatorName = implode(' ', $this->operators);
    }

    /**
     * @param string $operator
     *
     * @return string
     *
     * @throws InvalidSearchFilterSyntaxException
     * @throws UnexpectedException
     */
    public function validateOperator($operator)
    {
        if (!is_string($operator) || !strlen($operator)) {
            throw new UnexpectedException('Given operator needs to be a string.');
        }

        if (strlen($operator) > 32) {
            throw new InvalidSearchFilterSyntaxException(sprintf("The operator '%s...' must contain less then 32 characters.", addslashes(substr($operator, 0, 32))));
        }

        return strtoupper($operator);
    }

    /**
     * @param string $value
     */
    public function addValue($value)
    {
        $this->validateValue($value);

        $this->values[] = $value;
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

    /**
     * @return bool
     */
    public function hasValues()
    {
        return count($this->values) > 0;
    }
}
