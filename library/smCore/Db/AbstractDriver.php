<?php

/**
 * smCore Database Driver - Abstract
 *
 * Provides some pass-through functions to make things easy on developers
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

abstract class AbstractDriver
{
	protected $_connection;
	protected $_options = array();

	public function query(array $options = array())
	{
		return new Query($this, $options);
	}

	public function expr($sql, array $params = array(), array $param_types = array())
	{
		return new Expression($sql, $params, $param_types);
	}

	public function getOption($key)
	{
		if (array_key_exists($key, $this->_options))
		{
			return $this->_options[$key];
		}

		return null;
	}
}