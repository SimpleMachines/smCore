<?php

/**
 * smCore Dependency Injection Container
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
	 * Set a dependency for use later.
	 *
	 * @param string $key   The name of this value
	 * @param mixed  $value The value to store. Passing null will unset the key from the registry.
	 *
	 * @return mixed Returns the value that was set.
	 */
	public function offsetSet($key, $value)
	{
		if (is_callable($value))
		{
			$this->add($key, $value);
			return;
		}

		return $this->_registry[$key] = $value;
	}

	/**
	 * Get a value from the dependency register.
	 *
	 * @param string $key The name of the value to look for
	 *
	 * @return mixed Returns the value matched with the key passed, or null if nothing was found.
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

	public function offsetExists($key)
	{
		return isset($this->_registry[$key]);
	}

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
	 */
	public function add($key, $callback, array $arguments = array())
	{
		// @todo: throw an exception for invalid/duplicate/late
		if (empty($key) || array_key_exists($key, $this->_registry) || array_key_exists($key, $this->_lazy_loaders))
		{
			return;
		}

		if (!is_callable($callback))
		{
			throw new Exception(sprintf('Invalid callback registered for lazy loader "%s"', $key));
		}

		array_unshift($arguments, $this);

		$this->_lazy_loaders[$key] = array($callback, $arguments);
	}

	/**
	 * Remove a lazy loader.
	 *
	 * @param string $key The name of the lazy loader to remove.
	 */
	public function remove($key)
	{
		if (isset($this->_lazy_loaders[$key]))
		{
			unset($this->_lazy_loaders[$key]);
		}
	}

	/**
	 * Check if a lazy loader is registered
	 *
	 * @param 
	 *
	 * @return 
	 */
	public function has($key)
	{
		return isset($this->_lazy_loaders[$key]);
	}
}