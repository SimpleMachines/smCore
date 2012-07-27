<?php

/**
 * smCore 
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

use smCore\Exception, smCore\Settings;

class Memcached extends AbstractDriver
{
	public function __construct($options)
	{
		if (!extension_loaded('memcached'))
			throw new Exception('The memcached extension is not loaded.');

		$options = array_merge(array(
			'servers' => array(),
			'persistent' => false,
			'connect_timeout' => 1,
			'retry_timeout' => 15,
		), $options);

		$this->_memcached = new \Memcached();
		$this->_memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $options['connect_timeout']);
		$this->_memcached->setOption(\Memcached::OPT_RETRY_TIMEOUT, $options['retry_timeout']);

		if (empty($options['servers']))
		{
			// Try the default server
			$this->_memcached->addServer('127.0.0.1', 11211, 1);
		}
		else
		{
			if (isset($options['servers']['host']))
				$options['servers'] = array($options['servers']);

			foreach ($options['servers'] as $server)
			{
				// @todo: make sure the info is valid. Maybe use ->addServers()?

				$this->_memcached->addServer($server['host'], $server['port'], $server['weight']);
			}
		}
	}

	public function load($key)
	{
		$value = $this->_memcached->get(Settings::UNIQUE_8 . $key);

		if (is_array($value) && isset($value[0]))
			return $value[0];

		return false;
	}

	public function save($key, $data, array $tags = array(), $ttl = null)
	{
		$lifetime = time() + ($ttl ?: self::DEFAULT_TTL);

		$this->_memcached->set(Settings::UNIQUE_8 . $key, array($data, time(), $lifetime), $lifetime);
	}

	public function test($key)
	{
		$value = $this->_memcached->get(Settings::UNIQUE_8 . $key);

		if (is_array($value) && isset($value[1]))
			return $value[1];

		return false;
	}

	public function remove($key)
	{
		$this->_memcached->delete(Settings::UNIQUE_8 . $key);
	}

	public function clean($mode, array $tags = array())
	{
	}

	public function getMetadata($key)
	{
	}
}