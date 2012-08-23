<?php

/**
 * smCore Container Class
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

class Container implements ArrayAccess
{
	protected $_registry = array();
	protected $_lazy_loaders = array();

	/**
	 * ArrayAccess - Set a value in the internal registry
	 *
	 * @param string $key   The name of this value
	 * @param mixed  $value The value to store
	 *
	 * @return mixed Returns the value that was set
	 */
	public function offsetSet($key, $value)
	{
		return $this->_registry[$key] = $value;
	}

	/**
	 * ArrayAccess - Get a value from the internal registry.
	 *
	 * @param string $key The name of the value to look for
	 *
	 * @return mixed Returns the value matched with the key passed, or null if nothing was found
	 */
	public function offsetGet($key)
	{
		if (isset($this->_registry[$key]))
		{
			return $this->_registry[$key];
		}

		if (isset($this->_lazy_loaders[$key]))
		{
			return $this->_registry[$key] = call_user_func_array($this->_lazy_loaders[$key][0], $this->_lazy_loaders[$key][1]);
		}

		return null;
	}

	/**
	 * ArrayAccess - Check if a key exists in the internal registry.
	 *
	 * @param string $key The key to unset
	 *
	 * @return boolean True if the offset exists in the internal registry, false otherwise
	 */
	public function offsetExists($key)
	{
		return isset($this->_registry[$key]);
	}

	/**
	 * ArrayAccess - Unset a key from the internal registry.
	 *
	 * @param string $key The key to unset
	 */
	public function offsetUnset($key)
	{
		unset($this->_registry[$key]);
	}

	/**
	 * Add a lazy loader for a dependency.
	 *
	 * @param string   $key       The key to store the result of the callback under
	 * @param callback $callback  A callback to run
	 * @param array    $arguments Arguments to pass to the callback, optional
	 *
	 * @return self
	 */
	public function add($key, $callback, array $arguments = array())
	{
		// @todo: throw an exception for invalid/duplicate/late
		if (empty($key) || array_key_exists($key, $this->_registry) || array_key_exists($key, $this->_lazy_loaders))
		{
			return $this;
		}

		if (!is_callable($callback))
		{
			throw new Exception(sprintf('Invalid callback registered for lazy loader "%s"', $key));
		}

		$this->_lazy_loaders[$key] = array($callback, $arguments);

		return $this;
	}

	/**
	 * Remove a lazy loader.
	 *
	 * @param string $key The name of the lazy loader to remove.
	 *
	 * @return self
	 */
	public function remove($key)
	{
		if (isset($this->_lazy_loaders[$key]))
		{
			unset($this->_lazy_loaders[$key]);
		}

		return $this;
	}

	/**
	 * Check if a lazy loader is registered
	 *
	 * @param string $key The name of the lazy loader to check for.
	 *
	 * @return boolean
	 */
	public function has($key)
	{
		return isset($this->_lazy_loaders[$key]);
	}
}