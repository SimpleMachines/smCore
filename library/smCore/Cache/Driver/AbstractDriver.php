<?php

/**
 * smCore Cache Abstract Driver
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

namespace smCore\Cache\Driver;

abstract class AbstractDriver
{
	const DEFAULT_TTL = 3600;

	protected $_options;

	/**
	 * Loads data from the cache.
	 * 
	 * @param string $key            The key that the data is stored under.
	 * @param mixed  $failure_return Value to return on failure, default false
	 *
	 * @return mixed The data from the cache or $failure_return on failure
	 */
	abstract public function load($key, $failure_return = false);

	/**
	 * Saves data into the cache.
	 * 
	 * @param string $key      A string which the data is to be stored under.
	 * @param mixed  $data     The data to be stored (null will remove the entry)
	 * @param int    $lifetime How long should it be before we remove this piece of data from the cache?
	 */
	abstract public function save($key, $data, $lifetime = null);

	/**
	 * Check if data for a key has been stored in the cache.
	 *
	 * @param string $key The key to check
	 *
	 * @return boolean
	 */
	abstract public function test($key);

	/**
	 * Removes a cache item with key $key from the file cache.
	 * 
	 * @param string $key The key of the item to remove.
	 */
	abstract public function remove($key);

	/**
	 * Flush the entire cache
	 */
	abstract public function flush();

	/**
	 * Gets normalized information about a cached item
	 *
	 * @param string $key The key to retrieve metadata for
	 *
	 * @return array
	 */
	abstract public function getMetadata($key);

	/**
	 * Get an array of data about this cache driver
	 *
	 * @return array
	 */
	abstract public function getStats();

	/**
	 * Normalize a cache key
	 *
	 * @param string $key The cache key to normalize
	 *
	 * @return string The cache key, but with invalid characters replaced by underscores.
	 */
	protected function _normalize($key)
	{
		return preg_replace('/[^a-z0-9_\.\-]/i', '_', $key);
	}
}