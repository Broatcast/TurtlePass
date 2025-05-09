<?php

namespace UniqueLibs\QueryBuilderOperationBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use PHPSQLParser\PHPSQLParser;
use Symfony\Component\HttpFoundation\Request;
use UniqueLibs\QueryBuilderOperationBundle\Container\AttributeTypes\AbstractAttributeType;
use UniqueLibs\QueryBuilderOperationBundle\Container\FilteredMapper;
use UniqueLibs\QueryBuilderOperationBundle\Container\SearchFilterConfiguration;
use UniqueLibs\QueryBuilderOperationBundle\Container\SearchProperty;
use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;
use UniqueLibs\QueryBuilderOperationBundle\Exception\UnexpectedException;
use UniqueLibs\QueryBuilderOperationBundle\Filter\QueryFunction\QueryFunctionInterface;

class SearchFilter extends AbstractFilter
{
    /**
     * @var SearchFilterConfiguration
     */
    protected $searchFilterConfiguration;

    /**
     * @var array
     */
    protected $filteredMapping;

    /**
     * @var SearchProperty
     */
    protected $searchProperty;

    /**
     * @param string                    $name
     * @param SearchFilterConfiguration $searchFilterConfiguration
     */
    public function __construct($name, SearchFilterConfiguration $searchFilterConfiguration)
    {
        parent::__construct($name);

        $this->searchFilterConfiguration = $searchFilterConfiguration;
    }

    /**
     * @param FilteredMapper $filteredMapper
     * @param Request        $request
     * @param QueryBuilder   $queryBuilder
     *
     * @return QueryBuilder
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function executeRequest(FilteredMapper $filteredMapper, Request $request, QueryBuilder $queryBuilder)
    {
        $this->filteredMapping = $filteredMapper->getAttributes();

        $result = $this->getParsedDataFromInput($request->get('query'), $queryBuilder);

        return $this->executeParsedInput($result, $queryBuilder);
    }

    /**
     * @param FilteredMapper $filteredMapper
     * @param QueryBuilder   $queryBuilder
     * @param string         $string
     *
     * @return QueryBuilder
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function executeString(FilteredMapper $filteredMapper, QueryBuilder $queryBuilder, $string)
    {
        $this->filteredMapping = $filteredMapper->getAttributes();

        $result = $this->getParsedDataFromInput($string, $queryBuilder);

        return $this->executeParsedInput($result, $queryBuilder);
    }

    /**
     * @param string       $result
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function executeParsedInput($result, QueryBuilder $queryBuilder)
    {
        if (is_null($result)) {
            return $queryBuilder;
        }

        $output = $this->parse($queryBuilder, $result);

        if (!is_null($output)) {
            $queryBuilder->andWhere($output);
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $result
     *
     * @return QueryBuilder
     *
     * @throws UnexpectedException
     * @throws InvalidSearchFilterSyntaxException
     */
    public function parse(QueryBuilder $queryBuilder, array $result)
    {
        $this->searchProperty = new SearchProperty();
        $condition = null;
        $expressions = array();

        $returnExpression = null;

        for ($i = 0; $i < count($result); ++$i) {
            $data = &$result[$i];

            if (!array_key_exists('expr_type', $data)) {
                throw new UnexpectedException('Did not found expr_type in parsed result.');
            }

            if ($data['expr_type'] == 'bracket_expression') {
                 if (!$data['sub_tree']) {
                    throw new UnexpectedException('Subtree needed in bracket_expression.');
                }

                if ($this->searchProperty->hasColumn() && $this->searchProperty->hasOperators() && !$this->searchProperty->hasValues()) {
                    $values = $this->getValuesFromBrackedExpression($data['sub_tree']);

                    $expressions[] = $this->execute($queryBuilder, $values);
                } else if (!$this->searchProperty->hasColumn() && !$this->searchProperty->hasOperators() && !$this->searchProperty->hasValues()) {
                    $output = $this->parse($queryBuilder, $data['sub_tree']);

                    if (!is_null($output)) {
                        $expressions[] = $output;
                    }
                } else {
                    throw new UnexpectedException('Invalid position of bracket_expression.');
                }
            } else if ($data['expr_type'] == 'in-list') {
                if (!$data['sub_tree']) {
                    throw new UnexpectedException('Subtree needed in in-list.');
                }

                if ($this->searchProperty->hasColumn() && $this->searchProperty->hasOperators() && !$this->searchProperty->hasValues()) {
                    $values = $this->getValuesFromBrackedExpression($data['sub_tree']);

                    $expressions[] = $this->execute($queryBuilder, $values);
                } else {
                    throw new UnexpectedException('Invalid position of bracket_expression.');
                }
            } else if ($data['expr_type'] == 'colref') {
                if ($data['sub_tree']) {
                    throw new InvalidSearchFilterSyntaxException('Subtree not allowed in colref.');
                }

                $column = $this->searchProperty->validateColumn($data['base_expr']);

                if ($this->searchProperty->hasColumn() || $this->searchProperty->hasOperators() || $this->searchProperty->hasValues()) {
                    $column = addslashes($column);
                    throw new InvalidSearchFilterSyntaxException(sprintf("Invalid syntax for column '%s'. Example: %s = 1 or %s = 'string'", $column, $column, $column));
                }

                if (!array_key_exists($column, $this->filteredMapping)) {
                    $column = addslashes($column);
                    throw new InvalidSearchFilterSyntaxException(sprintf("Given column '%s' not found.", $column));
                }

                $this->searchProperty->setColumn($column);
            } else if ($data['expr_type'] == 'operator' || $data['expr_type'] == 'reserved') {
                if ($data['sub_tree']) {
                    throw new UnexpectedException('Subtree not allowed in operator or reserved.');
                }

                $operator = $this->searchProperty->validateOperator($data['base_expr']);

                if ($operator == '&&' || $operator == '||' || $operator == 'AND' || $operator == 'OR') {
                    if ($operator == '&&' || $operator == 'AND') {
                        if (!is_null($condition) && $condition == 'OR') {
                            throw new InvalidSearchFilterSyntaxException('Mixed AND - OR not allowed.');
                        }

                        $condition = 'AND';
                    } else {
                        if (!is_null($condition) && $condition == 'AND') {
                            throw new InvalidSearchFilterSyntaxException('Mixed AND - OR not allowed.');
                        }

                        $condition = 'OR';
                    }

                    unset($searchProperty);
                    $searchProperty = new SearchProperty();
                } else {
                    if (!$this->searchProperty->hasColumn() || $this->searchProperty->hasValues()) {
                        $operator = addslashes($operator);
                        throw new InvalidSearchFilterSyntaxException(sprintf("Invalid syntax at operator '%s'", $operator));
                    }

                    $this->searchProperty->addOperator($operator);
                }
            } else if ($data['expr_type'] == 'const') {
                if ($data['sub_tree']) {
                    throw new UnexpectedException('Subtree not allowed in colref.');
                }

                $this->searchProperty->validateValue($data['base_expr']);

                if (!$this->searchProperty->hasColumn()) {
                    throw new InvalidSearchFilterSyntaxException('Invalid Syntax.');
                }

                if (!$this->searchProperty->hasOperators() || $this->searchProperty->hasValues()) {
                    $column = addslashes($this->searchProperty->getColumn());
                    throw new InvalidSearchFilterSyntaxException(sprintf("Invalid syntax for column '%s'. Example: %s = 1 or %s = 'string'", $column, $column, $column));
                }

                $expressions[] = $this->execute($queryBuilder, array($data['base_expr']));
            } else if ($data['expr_type'] == 'function') {
                if (!isset($data['sub_tree']) || !is_array($data['sub_tree'])) {
                    $data['sub_tree'] = array();
                }

                $values = $this->getValuesFromFunction($data['base_expr'], $data['sub_tree']);

                $expressions[] = $this->execute($queryBuilder, $values);
            }
        }

        if (count($expressions)) {
            if (is_null($condition)) {
                $condition = 'AND';
            }

            if ($condition == 'AND') {
                $returnExpression = call_user_func_array(array($queryBuilder->expr(), 'andX'), $expressions);
            } else if ($condition == 'OR') {
                $returnExpression = call_user_func_array(array($queryBuilder->expr(), 'orX'), $expressions);
            }
        }

        return $returnExpression;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $values
     *
     * @return bool
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    protected function execute(QueryBuilder $queryBuilder, array $values)
    {
        if (!count($values)) {
            unset($this->searchProperty);
            $this->searchProperty = new SearchProperty();

            return false;
        }

        $searchOperators = $this->searchFilterConfiguration->getSearchOperators();

        if (!array_key_exists($this->searchProperty->getOperatorKey(), $searchOperators)) {
            $operator = addslashes($this->searchProperty->getOperatorName());
            throw new InvalidSearchFilterSyntaxException(sprintf("Given search operator '%s' not found.", $operator));
        }

        /** @var AbstractAttributeType $attributeType */
        $attributeType = $this->filteredMapping[$this->searchProperty->getColumn()][1];

        $allowedOperators = $attributeType->getAllowedOperators();

        if (!in_array($this->searchProperty->getOperatorKey(), $allowedOperators)) {
            $operator = addslashes($this->searchProperty->getOperatorName());
            $column = addslashes($this->searchProperty->getColumn());
            throw new InvalidSearchFilterSyntaxException(sprintf("Given search operator '%s' not allowed on column '%s'.", $operator, $column));
        }

        $arrayAllowed = $searchOperators[$this->searchProperty->getOperatorKey()]->isArrayOperator();

        if ($arrayAllowed) {
            foreach ($values as &$value) {
                $value = trim($value, "'");
                $value = $attributeType->getDataByInput($value);
            }
        } else {
            if (count($values) > 1) {
                $operator = addslashes($this->searchProperty->getOperatorName());
                throw new InvalidSearchFilterSyntaxException(sprintf("Given search operator '%s' do not allow arrays.", $operator));
            }

            $values[0] = trim($values[0], "'");
            $values = $attributeType->getDataByInput($values[0]);
        }

        $operatorKey = $this->searchProperty->getOperatorKey();
        $column = $this->searchProperty->getColumn();

        unset($this->searchProperty);
        $this->searchProperty = new SearchProperty();

        return $searchOperators[$operatorKey]->execute(
            $queryBuilder,
            $this->filteredMapping[$column][0],
            $values
        );
    }

    /**
     * @param array $tree
     *
     * @return array
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    protected function getValuesFromBrackedExpression(array $tree)
    {
        $values = array();

        foreach ($tree as $item) {
            if ($item['expr_type'] == 'function') {
                if (!isset($item['sub_tree']) || !is_array($item['sub_tree'])) {
                    $item['sub_tree'] = array();
                }

                array_merge($this->getValuesFromFunction(strtolower($item['base_expr']), $item['sub_tree']));
            } else if ($item['expr_type'] == 'const') {
                $values[] = $item['base_expr'];
            } else {
                throw new InvalidSearchFilterSyntaxException('Invalid Syntax.');
            }
        }

        return $values;
    }

    /**
     * @param string $name
     * @param array  $tree
     *
     * @return array
     *
     * @throws UnexpectedException
     * @throws InvalidSearchFilterSyntaxException
     */
    protected function getValuesFromFunction($name, array $tree)
    {
        if (!is_string($name) || !strlen($name)) {
            throw new UnexpectedException('Function name has to be a string');
        }

        if (strlen($name) > 64) {
            throw new InvalidSearchFilterSyntaxException('Function name should not contain more than 64 characters.');
        }

        $parameters = $this->getValuesFromBrackedExpression($tree);

        $name = strtoupper($name);
        $queryFunction = $this->searchFilterConfiguration->getQueryFunction($name);

        if (!$queryFunction instanceof QueryFunctionInterface) {
            throw new InvalidSearchFilterSyntaxException(sprintf("Function '%s' does not exist.", $name));
        }

        $result = $queryFunction->execute($name, $parameters);

        if (is_string($result)) {
            return array($result);
        } else if(is_array($result)) {
            return $result;
        }

        throw new UnexpectedException(sprintf("Function '%s' needs to return a string or an array.", str_replace("'", '', substr($name, 0, 64))));
    }

    /**
     * @param string       $input
     * @param QueryBuilder $queryBuilder
     *
     * @return null
     *
     * @throws InvalidSearchFilterSyntaxException
     */
    public function getParsedDataFromInput($input, QueryBuilder $queryBuilder)
    {
        if (!$input) {
            return null;
        }

        $parser = new PHPSQLParser();
        $result = $parser->parse($input);

        if (!is_array($result) || count($result) != 1 || !array_key_exists('WHERE', $result)) {
            throw new InvalidSearchFilterSyntaxException('Invalid Syntax. Example: where column = 1');
        }

        return $result['WHERE'];
    }
}
