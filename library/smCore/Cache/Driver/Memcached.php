<?php

/**
 * smCore Cache Driver - Memcached
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

class Memcached extends AbstractDriver
{
	public function __construct(array $options)
	{
		if (!extension_loaded('memcached'))
		{
			throw new Exception('The memcached extension is not loaded.');
		}

		$this->_options = array_merge(array(
			'servers' => array(),
			'persistent' => false,
			'connect_timeout' => 1,
			'retry_timeout' => 15,
			'prefix' => '',
			'default_ttl' => self::DEFAULT_TTL,
		), $options);

		$this->_memcached = new \Memcached();
		$this->_memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->_options['connect_timeout']);
		$this->_memcached->setOption(\Memcached::OPT_RETRY_TIMEOUT, $this->_options['retry_timeout']);

		if (empty($this->_options['servers']))
		{
			// Try the default server
			$this->_memcached->addServer('127.0.0.1', 11211, 1);
		}
		else
		{
			if (isset($this->_options['servers']['host']))
			{
				$this->_options['servers'] = array($this->_options['servers']);
			}

			foreach ($this->_options['servers'] as $server)
			{
				// @todo: make sure the info is valid. Maybe use ->addServers()?

				$this->_memcached->addServer($server['host'], $server['port'], $server['weight']);
			}
		}
	}

	public function load($key)
	{
		$value = $this->_memcached->get($this->_options['prefix'] . $key);

		if (is_array($value) && isset($value[0]))
		{
			return $value[0];
		}

		return false;
	}

	public function save($key, $data, $ttl = null)
	{
		$lifetime = time() + ($ttl ?: $this->_options['default_ttl']);

		$this->_memcached->set($this->_options['prefix'] . $key, array($data, time(), $lifetime), $lifetime);
	}

	public function test($key)
	{
		$value = $this->_memcached->get($this->_options['prefix'] . $key);

		if (is_array($value) && isset($value[1]))
		{
			return $value[1];
		}

		return false;
	}

	public function remove($key)
	{
		$this->_memcached->delete($this->_options['prefix'] . $key);
	}

	public function clean($mode)
	{
	}

	public function getMetadata($key)
	{
	}
}