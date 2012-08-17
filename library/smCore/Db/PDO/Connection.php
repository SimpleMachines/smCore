<?php

/**
 * smCore Database Driver - PDO MySQL
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

namespace smCore\Db\PDO;

use smCore\Db\ConnectionInterface, smCore\Db\Query, smCore\Db\Expression, smCore\Db\Exception;
use PDO, Closure;

class Connection implements ConnectionInterface
{
	protected $_options = array();
	protected $_connection;
	protected $_queryCount = 0;

	/**
	 * Create a new PDO connection
	 *
	 * @param string $dsn      Data Source Name for connection to the database
	 * @param string $user     Database username, if applicable
	 * @param string $password Database password, if applicable
	 * @param array  $options  Array of driver options
	 */
	public function __construct($dsn, $user = null, $password = null, array $options = null)
	{
		$this->_connection = new PDO($dsn, $user, $password, $options);

		$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->_connection->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('\smCore\Db\PDO\Statement', array()));
		$this->_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$this->_connection->setAttribute(PDO::ATTR_AUTOCOMMIT, true);

		$this->_options = array_merge(array(
			'prefix' => '',
		), $options);
	}

	public function getConnection()
	{
		return $this->_connection;
	}

	public function query($sql, array $parameters = array(), array $options = array())
	{
		$this->_queryCount++;

		$found_parameters = array();

		if (false !== strpos($sql, '{'))
		{
			$options = $this->_options;

			// @todo: this whole section should be in its own function, and not in this file
			$sql = preg_replace_callback('/\{([a-z_]+)(?::([a-zA-Z0-9_-]+))?\}/', function($matches) use (&$found_parameters, $parameters, $options)
			{
				$default = null;

				if ($matches[1] === 'db_prefix')
				{
					return $options['prefix'];
				}

				// Were we given a default value for this?
				if (array_key_exists($matches[2], $parameters))
				{
					$value = Expression::typeSanitize($parameters[$matches[2]], $matches[1], $matches[2]);

					// We'll have to work some magic because PDO can't bind arrays
					if ($matches[1] === 'array_int' || $matches[1] === 'array_string')
					{
						$i = 0;

						foreach ($value as $val)
						{
							$found_parameters[$matches[2] . '_' . $i++] = $val;
						}

						return '(:' . $matches[2] . '_' . implode(', :' . $matches[2] . '_', range(0, count($value) - 1)) . ')';
					}

					$found_parameters[$matches[2]] = $value;
					return ':' . $matches[2];
				}

				throw new Exception(sprintf('Missing parameter for key "%s".', $matches[2]));
			}, $sql);

			$prepared = $this->_connection->prepare($sql);
			$prepared->execute($found_parameters);

			return $prepared;
		}

		return $this->_connection->query($sql);
	}

	public function insert($table, array $data, array $options = array())
	{
		$this->_queryCount++;

		$keys = array_keys($data);
		$values = array_values($data);

		$sql = "INSERT INTO " . ($this->getOption('prefix') ?: '') . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', array_fill(0, count($keys), '?')) . ")";

		$prepared = $this->_connection->prepare($sql);
		$prepared->execute($values);

		return $this->lastInsertId();
	}

	/**
	 * Convenience method to run a query or set of queries
	 *
	 * @param Closure $closure
	 */
	public function transactional(Closure $closure)
	{
		$this->beginTransaction();

		try
		{
			$closure($this);
			$this->commit();
		}
		catch (\Exception $e)
		{
			$this->rollback();
			throw $e;
		}
	}

	public function replace($table, array $data, array $options = array())
	{
		$this->_queryCount++;
	}

	public function update($table, array $data, $condition, array $options = array())
	{
		$this->_queryCount++;
	}

	public function lastInsertId()
	{
		return $this->_connection->lastInsertId();
	}

	public function quote($value)
	{
		return $this->_connection->quote($value);
	}

	/*
	public function select($columns, $table = null)
	{
	}
	public function expr($expression, array $parameters)
	{
	}
	*/

	/**
	 * Start a transaction
	 */
	public function beginTransaction()
	{
		if ($this->_connection->inTransaction())
		{
			// @todo
		}
		else
		{
			$this->_connection->beginTransaction();
		}
	}

	/**
	 * Commit the current transaction
	 */
	public function commit()
	{
		if (!$this->_connection->inTransaction())
		{
			return;
		}

		$this->_connection->commit();
	}

	/**
	 * Roll back the current transaction
	 *
	 * @return 
	 */
	public function rollBack()
	{
		if (!$this->_connection->inTransaction())
		{
			throw new Exception('You cannot roll back a transaction without first starting one.');
		}

		$this->_connection->rollBack();
	}

	/**
	 * Check whether or not we're in a transaction right now
	 *
	 * @return boolean
	 */
	public function inTransaction()
	{
		return $this->_connection->inTransaction();
	}

	/**
	 * Get the number of queries that have been run on this connection
	 *
	 * @return int
	 */
	public function getQueryCount()
	{
		return $this->_queryCount;
	}

	/**
	 * Set non-driver options for this connection
	 *
	 * @param array $options 
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $value)
		{
			$this->setOption($key, $value);
		}
	}

	/**
	 * Set a single non-driver option for this connection
	 *
	 * @param string $key   Option key
	 * @param mixed  $value Option value
	 */
	public function setOption($key, $value)
	{
		$this->_options[$key] = $value;
	}

	/**
	 * Get a non-driver option that was set for this connection
	 *
	 * @param  string $key [description]
	 * @return mixed       [description]
	 */
	public function getOption($key)
	{
		if (array_key_exists($key, $this->_options))
		{
			return $this->_options[$key];
		}

		return null;
	}
}