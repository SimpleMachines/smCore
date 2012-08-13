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

use smCore\Exception;

class File extends AbstractDriver
{
	protected $_options = array();

	/**
	 * This is here to satisfy AbstractDriver's conditions
	 * 
	 * @param array $options An array of cache options
	 */
	public function __construct($options)
	{
		$this->_options = array_merge(array(
			'prefix' => 'data',
			'default_ttl' => self::DEFAULT_TTL,
		), $options);

		if (!isset($this->_options['directory']))
		{
			throw new Exception('Missing required File cache option: directory.');
		}

		// Try to create the cache directory if it doesn't exist
		if (!is_dir($this->_options['directory']) && false === mkdir($this->_options['directory'], 0777, true))
		{
			throw new Exception('File cache directory not found, unable to automatically create it.');
		}

		// It's not writable - this is bad!
		if (!is_writable($this->_options['directory']))
		{
			throw new Exception('The file cache directory was found, but is not writable.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($key, $failure_return = false)
	{
		// this is a revised version of that from SMF 2.0
		$expired = null;

		$filename = $this->_getFilename($key);

		clearstatcache(true, $filename);

		if (file_exists($filename) && filesize($filename) > 10)
		{
			include $filename;

			if (empty($expired) && isset($value))
			{
				return @unserialize($value);
			}

			if ($expired)
			{
				$this->remove($key);
			}
		}

		return $failure_return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save($key, $data, $lifetime = null)
	{
		// if it's null then lets just remove the file
		if ($data === null || $lifetime < 0)
		{
			$this->remove($key);
			return;
		}

		// set our time to live
		$lifetime = time() + ($lifetime ?: $this->_options['default_ttl']);
		$filename = $this->_getFilename($key);

		if ($fh = @fopen($filename, 'w'))
		{
			// Write the file.
			set_file_buffer($fh, 0);
			$cache_data = '<' . '?php if (time() > ' . $lifetime . ') { $expired = true; } else { $expired = false; $value = \'' . addcslashes(serialize($data), '\\\'') . '\';}' . '?' . '>';
			$cache_bytes = 0;

			// Only write if we can obtain a lock
			if (flock($fh, LOCK_EX))
			{
				$cache_bytes = fwrite($fh, $cache_data);
			}

			flock($fh, LOCK_UN);
			fclose($fh);

			// Check that the cache write was successful; all the data should be written
			// If it fails due to low diskspace, remove the cache file
			if ($cache_bytes !== mb_strlen($cache_data))
			{
				@unlink($filename);
			}
			else
			{
				@chmod($filename, 0666 & ~umask());
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function test($key)
	{
		$filename = $this->_getFilename($key);
		clearstatcache(true, $filename);
		return file_exists($filename);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		@unlink($this->_getFilename($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function flush()
	{
		$files = glob($this->_options['directory'] . '/.smcore_data_*.php');

		foreach ($files as $file)
		{
			@unlink($file);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadata($key)
	{
		// not sure on this either
		return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStats()
	{
		return array(
			'name' => 'File',
			'items' => 0,
			'hits' => 0,
			'misses' => 0,
			'servers' => array('N/A'),
		);
	}

	/**
	 * Internal method to create a normalized cache filename
	 *
	 * @param string $key 
	 *
	 * @return string 
	 */
	protected function _getFilename($key)
	{
		return $this->_options['directory'] . '/.smcore_data_' . $this->_normalize($key) . '.php';
	}
}