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

	protected $_driver;
	protected $_options;

	protected $_selects;
	protected $_from = array();
	protected $_joins = array();
	protected $_where;
	protected $_limit;
	protected $_group_by;
	protected $_order_by;

	protected $_expressionCount;
	protected $_parameters;

	public function __construct(AbstractDriver $driver, array $options = array())
	{
		$this->_driver = $driver;
		$this->_dirty = true;

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

	public function where($condition)
	{
		$this->_dirty = true;

		if ($condition instanceof Expression)
		{
			$this->_where = $condition;
		}
		else
		{
			$this->_where = new Expression($condition);
		}

		return $this;
	}

	public function limit($offset, $row_count)
	{
		$this->_limit = array((int) $offset, (int) $row_count);

		return $this;
	}

	public function join($table, $as = null, $condition = null, $type = 'inner')
	{
		$this->_dirty = true;

		if (null === $as)
		{
			$as = $table;
		}

		if (null !== $condition && !$condition instanceof Expression)
		{
			$condition = new Expression($condition);
		}
		else if (empty($condition))
		{
			// We don't want "INNER JOIN table AS t ON ()"
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

	public function leftJoin($table, $as = null, $condition = null)
	{
		return $this->join($table, $as, $condition, 'left');
	}

	public function execute()
	{
	}

	public function fetchOne()
	{
		return new Result();
	}

	public function fetchAll()
	{
		return new Result();
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
		$this->_expressionCount = 0;
		$this->_parameters = array();

		if (!$this->_dirty)
		{
			return $this->_sql;
		}

		if (empty($this->_selects))
		{
			throw new Exception('Please choose one or more columns to select data from.');
		}

		if (empty($this->_from))
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
		$table_prefix = !$this->_options['no_prefix'] ? ($this->_driver->getOption('prefix') ?: '') : '';

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

		$this->_sql .= "\nFROM " . $table_prefix . $this->_from[0] . ' AS ' . $this->_from[1];

		if (!empty($this->_joins))
		{
			foreach ($this->_joins as $join)
			{
				switch ($join['type'])
				{
					case 'left':
					case 'right':
					case 'inner':
					case 'outer':
					case 'full':
						$this->_sql .= "\n" . strtoupper($join['type']) . " JOIN " . $join['table'][0] . " AS " . $join['table'][1];

						if (null !== $join['condition'])
						{
							list ($condition, $parameters) = $this->_formatExpression($join['condition']);
							$this->_parameters += $parameters;
							$this->_sql .= " ON (" . $condition . ")";
						}
				}
			}
		}

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
				$this->_order_by = array("NULL");
			}
		}

		// Order by
		if (null !== $this->_order_by)
		{
			$this->_sql .= "\nORDER BY " . $this->_order_by;
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
		if ($expr->getType() === Expression::TYPE_POSITIONAL)
		{
			$count = ++$this->_expressionCount;

			$condition = preg_replace_callback('/:(p[0-9]+)/', function($match) use ($count)
			{
				return ':e' . $count . $match[1];
			}, $expr->getSQL());

			$raw_parameters = $expr->getParameters();

			$parameters = array();

			foreach ($raw_parameters as $key => $param)
			{
				$parameters['e' . $count . $key] = $param;
			}
		}
		else
		{
			$condition = $expr->getSQL();

			$parameters = $expr->getParameters();
		}

		return array($condition, $parameters);
	}

	public function getParameters()
	{
		if ($this->_dirty)
		{
			$this->getSQL();
		}

		return $this->_parameters;
	}









}