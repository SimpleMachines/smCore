<?php

/**
 * smCore Query Builder Query
 *
 * @package smCore
 * @author smCore Dev Team
 * @license MPL 1.1
 * @version 1.0 Alpha
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 */

namespace smCore\Db;

use smCore\Exception;

class Query
{
	protected $_sql;
	protected $_dirty;

	protected $_connection;
	protected $_options;

	protected $_selects;
	protected $_from;
	protected $_joins;
	protected $_where;
	protected $_having;
	protected $_limit;
	protected $_group_by;
	protected $_order_by;

	protected $_expression_count;
	protected $_parameters;
	protected $_bound_parameters;

	public function __construct(ConnectionInterface $connection, array $options = array())
	{
		$this->_connection = $connection;
		$this->_dirty = true;

		$this->_joins = array();
		$this->_bound_parameters = array();

		$this->_options = array_merge(array(
			'no_prefix' => false,
		), $options);
	}

	public function select($columns, $table = 0)
	{
		$this->_dirty = true;

		if (!empty($this->_selects[$table]))
		{
			$this->_selects[$table] = array_merge($this->_selects[$table], $columns);
		}
		else
		{
			$this->_selects[$table] = (array) $columns;
		}

		return $this;
	}

	public function from($table, $as = null)
	{
		$this->_dirty = true;

		if (null === $as)
		{
			$as = $table;
		}

		$this->_from = array($table, $as);

		return $this;
	}

	public function where($condition, array $parameters = array())
	{
		$this->_dirty = true;

		if ($condition instanceof Expression)
		{
			$this->_where = $condition;
		}
		else
		{
			$this->_where = new Expression($condition, $parameters);
		}

		return $this;
	}

	public function limit($offset, $row_count)
	{
		$this->_limit = array((int) $offset, (int) $row_count);

		return $this;
	}

	public function join($table, $as = null, $condition = null, array $parameters = array(), $type = 'inner')
	{
		$this->_dirty = true;

		if (null === $as)
		{
			$as = $table;
		}

		if (null !== $condition && !$condition instanceof Expression)
		{
			$condition = new Expression($condition, $parameters);
		}
		else if (empty($condition))
		{
			// We don't want "INNER JOIN table AS t ON ()" when we're passed an empty string
			$condition = null;
		}

		switch (strtolower($type))
		{
			case 'inner':
			case 'full':
			case 'left':
			case 'outer':
			case 'right':
				$this->_joins[] = array(
					'type' => $type,
					'table' => array($table, $as),
					'condition' => $condition,
				);
				break;
			default:
				throw new Exception(sprintf("Unknown join type: %s", $type));
		}

		return $this;
	}

	public function leftJoin($table, $as = null, $condition = null, array $parameters = array())
	{
		return $this->join($table, $as, $condition, $parameters, 'left');
	}

	public function rightJoin($table, $as = null, $condition = null, array $parameters = array())
	{
		return $this->join($table, $as, $condition, $parameters, 'right');
	}

	public function innerJoin($table, $as = null, $condition = null, array $parameters = array())
	{
		return $this->join($table, $as, $condition, $parameters, 'inner');
	}

	public function outerJoin($table, $as = null, $condition = null, array $parameters = array())
	{
		return $this->join($table, $as, $condition, $parameters, 'outer');
	}

	public function orderBy($expression, $order = 'ASC', $prepend = false)
	{
		$this->_dirty = true;

		$order = strtoupper($order);

		if ($order !== 'ASC' && $order !== 'DESC')
		{
			throw new Exception('orderBy() second parameter must be "ASC" or "DESC".');
		}

		if (!$expression instanceof Expression)
		{
				$expression = array(new Expression($expression), $order);
		}

		if (!$prepend)
		{
			$this->_order_by[] = array($expression, $order);
		}
		else
		{
			array_unshift($this->_order_by, $expression);
		}

		return $this;
	}

	public function having($condition)
	{
		$this->_dirty = true;

		if ($condition instanceof Expression)
		{
			$this->_having = $condition;
		}
		else
		{
			$this->_having = new Expression($condition);
		}

		return $this;
	}

	public function groupBy($expression, $order = 'ASC')
	{
		$this->_dirty = true;

		if ($expression instanceof Expression)
		{
			$this->_group_by[] = $expression;
		}
		else
		{
			$this->_group_by[] = new Expression($expression);
		}

		return $this;
	}

	public function __toString()
	{
		try
		{
			return $this->getSQL();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}

	public function getSQL()
	{
		$this->_expression_count = 0;
		$this->_parameters = array();

		if (!$this->_dirty)
		{
			return $this->_sql;
		}

		if (empty($this->_selects))
		{
			throw new Exception('Please choose one or more columns to select data from.');
		}

		if (null === $this->_from)
		{
			if (1 === count($this->_selects) && 0 !== $key = key($this->_selects))
			{
				$this->_from = array($key, $key);
			}
			else
			{
				throw new Exception('Please choose a table to select data from.');
			}
		}

		$this->_sql = 'SELECT ';

		$table_aliases = array($this->_from[0] => $this->_from[1]);
		$table_prefix = !$this->_options['no_prefix'] ? ($this->_connection->getOption('prefix') ?: '') : '';

		$selects = array();

		foreach ($this->_selects as $table => $select)
		{
			if (0 === $table)
			{
				$table = $this->_from[0];
			}
			else if (!empty($table_aliases[$table]))
			{
				$table = $table_aliases[$table];
			}

			foreach ($select as $as => $column)
			{
				if (!is_int($as))
				{
					$selects[] = $table . '.' . $column . ' AS ' . $as;
				}
				else
				{
					$selects[] = $table . '.' . $column;
				}
			}
		}

		$this->_sql .= implode(', ', $selects);

		// From
		$this->_sql .= "\nFROM " . $table_prefix . $this->_from[0] . ' AS ' . $this->_from[1];

		// Joins
		if (!empty($this->_joins))
		{
			foreach ($this->_joins as $join)
			{
				$this->_sql .= "\n" . strtoupper($join['type']) . " JOIN " . $table_prefix . $join['table'][0] . " AS " . $join['table'][1];

				if (null !== $join['condition'])
				{
					list ($condition, $parameters) = $this->_formatExpression($join['condition']);
					$this->_parameters += $parameters;
					$this->_sql .= " ON (" . $condition . ")";
				}
			}
		}

		// Where
		if (!empty($this->_where))
		{
			$this->_sql .= "\nWHERE ";

			list ($condition, $parameters) = $this->_formatExpression($this->_where);
			$this->_parameters += $parameters;
			$this->_sql .= $condition;
		}

		// Group by
		if (null !== $this->_group_by)
		{
			// Add an "order by" to prevent MySQL from doing unnecessary filesorts
			if (null === $this->_order_by)
			{
				$this->_sql .= "\nORDER BY NULL";
			}
		}

		// Order by
		if (null !== $this->_order_by)
		{
			$first = true;

			foreach ($this->_order_by as $clause)
			{
				if ($clause === null)
				{
					$this->_sql .= "ORDER BY NULL";
				}
				else
				{
					if ($first)
					{
						$this->_sql .= sprintf("\nORDER BY %s %s", $clause);
						$first = false;
					}
					else
					{
						$this->_sql .= sprintf(", %s %s", $clause);
					}
				}
			}
		}

		// Limit
		if (null !== $this->_limit)
		{
			$this->_sql .= "\nLIMIT " . $this->_limit[0] . ", " . $this->_limit[1];
		}

		$this->_dirty = false;
		return $this->_sql;
	}

	protected function _formatExpression(Expression $expr)
	{
		$condition = $expr->getSQL();

		$parameters = $expr->getParameters();

		return array($condition, $parameters);
	}

	public function bindParam($key, $value)
	{
		$this->_bound_parameters[$key] = $value;

		return $this;
	}

	public function bindParams(array $parameters)
	{
		foreach ($parameters as $key => $value)
		{
			$this->_bound_parameters[$key] = $value;
		}

		return $this;
	}

	public function execute()
	{
		$sql = $this->getSQL();

		$parameters = $this->_parameters;

		foreach ($parameters as $key => $parameter)
		{
			if (array_key_exists($key, $this->_bound_parameters))
			{
				$parameters[$key] = Expression::typeSanitize($this->_bound_parameters[$key], $parameter[0], $key);
			}
			else
			{
				$parameters[$key] = $parameter[1];
			}

			if (0 === strpos($parameter[0], 'array_') && 1 < $count = count($parameters[$key]))
			{
				// We have to reformat the query, since PDO can't bind arrays of values
				$str = ':' . $key . '_' . implode(', :' . $key . '_', range(0, $count - 1));

				$sql = str_replace(':' . $key, $str, $sql);

				foreach ($parameters[$key] as $num => $value)
				{
					$parameters[$key . '_' . $num] = $value;
				}

				unset($parameters[$key]);
			}
		}

		return $this->_connection->execute($this->getSQL(), $parameters);
	}

	public function fetch()
	{
		return $this->execute()->fetch();
	}

	public function fetchAll()
	{
		return $this->execute()->fetchAll();
	}

	public function rowCount()
	{
		return $this->execute()->rowCount();
	}








}