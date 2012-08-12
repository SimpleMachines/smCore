<?php

/**
 * smCore Cache Driver - APC
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

use smCore\Exception;

class Apc extends AbstractDriver
{
	public function __construct(array $options)
	{
		if (!extension_loaded('apc'))
		{
			throw new Exception('The APC extension is not loaded.');
		}

		$this->_options = array_merge(array(
			'prefix' => '',
			'default_ttl' => self::DEFAULT_TTL,
		), $options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($key, $failure_return = false)
	{
		$value = apc_fetch($this->_options['prefix'] . $key);

		if (is_array($value) && isset($value[0]))
		{
			return $value[0];
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save($key, $data, $ttl = null)
	{
		$lifetime = time() + ($ttl ?: $this->_options['default_ttl']);

		apc_store($this->_options['prefix'] . $key, array($data, time(), $lifetime), $lifetime);
	}

	/**
	 * {@inheritdoc}
	 */
	public function test($key)
	{
		return apc_exists($this->_options['prefix'] . $key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		return apc_delete($this->_options['prefix'] . $key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function flush()
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadata($key)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStats()
	{
		$stats = apc_cache_info("user", true);

		return array(
			'name' => 'APC',
			'items' => $stats['num_entries'],
			'hits' => $stats['num_hits'],
			'misses' => $stats['num_misses'],
			'servers' => array(),
		);
	}
}