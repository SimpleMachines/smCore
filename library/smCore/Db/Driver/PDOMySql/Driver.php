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

namespace smCore\Db\Driver\PDOMySql;

use smCore\Db\AbstractDriver, smCore\Db\PDO\Connection;
use PDO, PDOException, Exception;

class Driver extends AbstractDriver
{
	public function __construct(array $options)
	{
		$this->_options = array_merge(array(
			'unix_socket' => '',
			'host' => 'localhost',
			'port' => 3306,
			'user' => '',
			'password' => '',
			'dbname' => '',
			'prefix' => 'smcore_',
			'driver_options' => array(),
		), $options);

		if (!empty($options['unix_socket']))
		{
			$dsn = "mysql:host=localhost;unix_socket=" . $this->_options['unix_socket'] . ";dbname=" . $this->_options['dbname'];
		}
		else
		{
			$dsn = "mysql:host=" . $this->_options['host'] . (!empty($this->_options['port']) ? ';port=' . $this->_options['port'] : '') . ";dbname=" . $this->_options['dbname'];
		}

		// Force 1002 (PDO::MYSQL_ATTR_INIT_COMMAND) to ensure we're working with UTF8
		$driver_options = array_merge($this->_options['driver_options'], array(1002 => "SET NAMES 'utf8'"));

		$this->_connection = new Connection($dsn . ';charset=utf8', $this->_options['user'], $this->_options['password'], $driver_options);

		unset($this->_options['password']);

		$this->_connection->setOptions($this->_options);
	}

	// ^SQLSTATE\[([A-Z0-9]+)\]: ([^:]+): ([0-9]+) (.*)$
	// SQLSTATE[42S22]: Column not found: 1054 Unknown column 'dne' in 'field list'
	// SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'INSERTAINTO smcore_test (id, name, dne) VALUES ('1', 'Steven', 'hi')' at line 11
}