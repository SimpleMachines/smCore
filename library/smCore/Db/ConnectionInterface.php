<?php

/**
 * smCore Database Connection Interface
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

use Closure;

interface ConnectionInterface
{
	public function query($sql, array $parameters = array(), array $options = array());

	public function insert($table, array $data, array $options = array());
	public function replace($table, array $data, array $options = array());
	public function update($table, array $data, $condition, array $options = array());

	public function lastInsertId();
	public function quote($value);

	// public function select($columns, $table = null);
	// public function expr($expression, array $parameters);

	public function setOptions(array $options);
	public function setOption($key, $value);
	public function getOption($key);

	public function transactional(Closure $closure);
	public function beginTransaction();
	public function commit();
	public function rollBack();
	public function inTransaction();

	public function getQueryCount();
}