<?php

/**
 * smCore Abstract Settings Class
 *
 * With lazy documentation!
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

namespace smCore;

use ArrayAccess;

abstract class Settings implements ArrayAccess
{
	protected $_settings = array();

	public function __construct()
	{
		$this->_validateSettings();

		// Add a few things if they weren't already set.
		if (!isset($this->_settings['cache']['prefix']))
		{
			$this->_settings['cache']['prefix'] = $this->_settings['site_key'];
		}

		if (!isset($this->_settings['cache']['directory']))
		{
			$this->_settings['cache']['directory'] = $this->_settings['cache_dir'];
		}
	}

	protected function _validateSettings()
	{
		// Checks for the absolute minimum settings needed to run the site
		// @todo
	}

	public function offsetExists($key)
	{
		return isset($this->_settings[$key]);
	}

	public function offsetSet($key, $value)
	{
		$this->_settings[$key] = $value;
	}

	public function offsetGet($key)
	{
		if (isset($this->_settings[$key]))
		{
			return $this->_settings[$key];
		}

		return null;
	}

	public function offsetUnset($key)
	{
		unset($this->_settings[$key]);
	}

	/**
	 * Get a stored setting
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		if (isset($this->_settings[$key]))
		{
			return $this->_settings[$key];
		}

		return null;
	}
}