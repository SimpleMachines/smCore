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

use PDO, Closure, Exception;
use smCore\Db\ConnectionInterface, smCore\Db\Query, smCore\Db\Expression;

class Connection extends PDO implements ConnectionInterface
{
	protected $_options = array();

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
		parent::__construct($dsn, $user, $password, $options);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('\smCore\Db\PDO\Statement', array()));
	}

	public function query(array $options = array())
	{
		return new Query($this, $options);
	}

	public function execute($sql, array $parameters = null)
	{
		if ($sql instanceof Query)
		{
			$result = $sql->execute($parameters);
		}
		else
		{
			$result = $this->execute($sql);
		}
	}

	public function insert($table, $data)
	{
		$keys = array_keys($data);
		$values = array_values($data);

		$sql = "INSERT INTO " . ($this->getOption('prefix') ?: '') . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', array_fill(0, count($keys), '?')) . ")";

		$prepared = $this->prepare($sql);
		$prepared->execute($values);

		return $this->lastInsertId();
	}

	public function replace($table, $data)
	{
		$keys = array_keys($data);
		$values = array_values($data);

		$sql = "REPLACE INTO " . ($this->getOption('prefix') ?: '') . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', array_fill(0, count($keys), '?')) . ")";

		$prepared = $this->prepare($sql);
		$prepared->execute($values);

		return $this->lastInsertId();
	}

	public function update($table, $data, $expression)
	{
		if (is_array($expression) && 0 !== $key = key($expression))
		{
			$expression = $this->expr($key . ' = {string:' . $key . '}', $expression);
		}
		else if (is_string($expression))
		{
			$expression = $this->expr($expression);
		}
		else if (!$expression instanceof Expression)
		{
			throw new Exception('update() expects an array, string, or Expression.');
		}

		$keys = array_keys($data);

		$sql = "UPDATE " . ($this->getOption('prefix') ?: '') . $table . " SET ";

		$first = true;
		foreach ($keys as $key)
		{
			$sql .= $key . " = :" . $key . (!$first ? ', ' : '');
			$first = false;
		}

		$sql .= " WHERE "  . $expression->getSQL();

die(print_r($expression->getParameters()));

		$prepared = $this->prepare($sql);
		$prepared->execute($values);

		return $prepared->rowCount();
	}

	public function expr($sql, array $params = array(), array $param_types = array())
	{
		return new Expression($sql, $params, $param_types);
	}

	/**
	 * Convenience method to run a query or set of queries
	 *
	 * @param  Closure $closure
	 */
	public function transactional(Closure $closure)
	{
		$this->beginTransaction();

		try
		{
			$closure($this);
			$this->commit();
		}
		catch (Exception $e)
		{
			$this->rollback();
			throw $e;
		}
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