<?php

/**
 * smCore Query Builder Expression
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
use DateTime;

class Expression
{
	protected $_sql;
	protected $_params;
	protected $_type;

	const TYPE_POSITIONAL = 1;
	const TYPE_NAMED = 2;

	public function __construct($sql, array $params = array(), array $param_types = array())
	{
		$this->_sql = $sql;
		$this->_params = array();

		// Positional parameters
		if (false !== strpos($sql, '?'))
		{
			$count = 0;
			$this->_type = self::TYPE_POSITIONAL;

			$this->_sql = preg_replace_callback('/\?/', function($match) use (&$count)
			{
				return ':p' . ++$count;
			}, $this->_sql);
		}

		// Named parameters
		if (false !== strpos($sql, ':'))
		{
			if ($this->_type === self::TYPE_POSITIONAL)
			{
				throw new Exception('You cannot mix positional and named parameters in an expression.');
			}

			$this->_type = self::TYPE_NAMED;
		}

		// 
		if (count($param_types) > 0 && count($params) !== count($param_types))
		{
			throw new Exception("Number of parameters does not match the number of parameter types.");
		}

		if (!empty($params))
		{
			if (empty($param_types))
			{
				$param_types = array_fill(0, count($params), 'text');
			}

			$i = 0;

			foreach ($params as $key => $value)
			{
				list ($type, $value) = $this->_typeSanitize($param_types[$i], $value);

				// Positional
				if ($this->_type === self::TYPE_POSITIONAL)
				{
					$this->_params['p' . ++$i] = array(
						'value' => $value,
						'type' => $type
					);
				}
				// Named
				else
				{
					$this->_params[$key] = array(
						'value' => $value,
						'type' => $type
					);
				}
			}
		}
	}

	protected function _typeSanitize($type, $value)
	{
		switch (strtolower($type))
		{
			case 'int':
			case 'integer':
			{
				if (!is_int($value) || (string) (int) $value !== (string) $value)
				{
					throw new Exception('');
				}

				return array('int', (int) $value);
			}
			case 'float':
			case 'double':
			{
				if (!is_numeric($value))
				{
					throw new Exception('');
				}

				return array('float', (float) $value);
			}
			case 'bool':
			case 'boolean':
			{
				return array('boolean', $value ? 1 : 0);
			}
			case 'str':
			case 'string':
			case 'text':
			{
				return array('string', (string) $value);
			}
			case 'date':
			{
				if ($value instanceof DateTime)
				{
				}
				else if (is_int($value))
				{
					// Unix epoch timestamp?
				}

				// YYYY-MM-DD
			}
			case 'datetime':
			{
				if ($value instanceof DateTime)
				{
				}
				else if (is_int($value))
				{
					// Unix epoch timestamp?
				}

				// YYYY-MM-DD HH:MM:SS
			}
			case 'array_int':
			case 'array_integer':
			{
				$checked = array();

				foreach ($value as $val)
				{
					if (!is_int($val) || (string) (int) $val !== (string) $val)
					{
						throw new Exception('Array of integers expected, but received a non-integer value.');
					}

					$checked[] = (int) $val;
				}

				return array('array_int', $checked);
			}
			case 'array_str':
			case 'array_string':
			case 'array_text':
			{
				$checked = array();

				foreach ($value as $val)
				{
					$checked[] = (string) $val;
				}

				return array('array_string', $checked);
			}
			default:
			{
				throw new Exception(sprintf("Invalid data type: %s", $type));
			}
		}
	}

	public function getSQL()
	{
		return $this->_sql;
	}

	public function getParameters()
	{
		return $this->_params;
	}

	public function getType()
	{
		return $this->_type;
	}

	public function __toString()
	{
		return $this->getSQL();
	}
}