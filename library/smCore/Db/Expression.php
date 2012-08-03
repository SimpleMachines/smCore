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

	public function __construct($sql, array $params = array())
	{
		$this->_sql = $sql;
		$found_parameters = array();

		if (false !== strpos($sql, '{'))
		{
			$this->_sql = preg_replace_callback('/\{([a-z_]+):([a-zA-Z0-9_-]+)\}/', function($matches) use (&$found_parameters, $params)
			{
				$default = null;

				// Were we given a default value for this?
				if (array_key_exists($matches[2], $params))
				{
					$default = Expression::typeSanitize($params[$matches[2]], $matches[1], $matches[2]);
				}

				$found_parameters[$matches[2]] = array($matches[1], $default);

				return ':' . $matches[2];
			}, $this->_sql);
		}
		else if (false !== strpos($sql, '?'))
		{
			// Positional
		}

		$this->_params = $found_parameters;
	}

	public static function typeSanitize($value, $type, $name = null)
	{
		$name = $name ? ' for parameter "' . $name . '"' : '';

		switch ($type)
		{
			case 'int':
			case 'integer':
			{
				if (!is_int($value) || (string) (int) $value !== (string) $value)
				{
					throw new Exception('Inorrect value sent' . $name . ', expected integer.');
				}

				return (int) $value;
			}
			case 'float':
			case 'double':
			{
				if (!is_numeric($value))
				{
					throw new Exception('Inorrect value sent' . $name . ', expected float.');
				}

				return (float) $value;
			}
			case 'bool':
			case 'boolean':
			{
				return $value ? 1 : 0;
			}
			case 'str':
			case 'string':
			case 'text':
			{
				return (string) $value;
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

				$value = (array) $value;

				foreach ($value as $val)
				{
					if (!is_int($val) || (string) (int) $val !== (string) $val)
					{
						throw new Exception('Inorrect value sent' . $name . ', expected an array of integers.');
					}

					$checked[] = (int) $val;
				}

				return $checked;
			}
			case 'array_str':
			case 'array_string':
			case 'array_text':
			{
				$checked = array();

				$value = (array) $value;

				foreach ($value as $val)
				{
					$checked[] = (string) $val;
				}

				return $checked;
			}
			default:
			{
				throw new Exception(sprintf("Unrecognized parameter type: %s", $type));
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

	public function __toString()
	{
		return $this->getSQL();
	}
}