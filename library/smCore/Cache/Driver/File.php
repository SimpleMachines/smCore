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

use smCore\Settings;

class File extends AbstractDriver
{
	/**
	 * Sets some local cache settings
	 * 
	 * @param array $opts An array of cache options
	 */
	public function __construct( $opts )
	{
		$this->DIR = Settings::$cache['dir'];
		$this->DEFAULT_TTL = parent::DEFAULT_TTL;
	}
	
	public function load($key)
	{
		// this is a revised version of of that from SMF 2.0
		$key = $this->makeKey($key);
		$value = null;
		if (file_exists($this->DIR . '/data_' . $key . '.php') && filesize($this->DIR . '/data_' . $key . '.php') > 10)
		{
			// php will cache file_exists et all, we can't 100% depend on its results so proceed with caution
			@include($this->DIR . '/data_' . $key . '.php');
			if (!empty($expired) && isset($value))
			{
				@unlink($this->DIR . '/data_' . $key . '.php');
				unset($value);
			}
		}
		return empty($value) ? null : @unserialize($value);
	}

	/**
	 * Saves data into the cache
	 * 
	 * @param string $key A string which the data is to be stored under.
	 * @param mixed $data The data to be stored (null will remove the entry)
	 * @param array $tags The tags this should be stored under (when resetting data in the cache)
	 * @param int $ttl How long should it be before we remove this piece of data from the cache?
	 */
	public function save($key, $data, array $tags = array(), $ttl = null)
	{
		// set our time to live
		$ttl = $ttl ? $ttl : $this->DEFAULT_TTL;
		// work out our data
		$value = $data === null ? null : serialize($data);
		// if it's null then lets just remove the file
		if ($value === null)
			$this->remove($key);
		else
		{
			// define our key
			$key = $this->makeKey($key);
			// build our file
			$cache_data = '<' . '?' . 'php if (' . (time() + $ttl) . ' < time()) $expired = true; else{$expired = false; $value = \'' . addcslashes($value, '\\\'') . '\';}' . '?' . '>';
			$fh = @fopen($this->DIR . '/data_' . $key . '.php', 'w');
			if ($fh)
			{
				// Write the file.
				set_file_buffer($fh, 0);
				flock($fh, LOCK_EX);
				$cache_bytes = fwrite($fh, $cache_data);
				flock($fh, LOCK_UN);
				fclose($fh);

				// Check that the cache write was successful; all the data should be written
				// If it fails due to low diskspace, remove the cache file
				if ($cache_bytes != strlen($cache_data))
					@unlink($this->DIR . '/data_' . $key . '.php');
			}
		}
	}

	/**
	 * 
	 */
	public function test($key)
	{
		// !!! what should this return?
		return;
	}

	/**
	 * 
	 * @param type $key
	 */
	public function remove($key)
	{
		@unlink($this->DIR . '/data_' . $this->makeKey($key) . '.php');
	}

	/**
	 * 
	 * @param type $mode
	 * @param array $tags
	 */
	public function clean($mode, array $tags = array())
	{
		// !!! would probably be better to empty the data_*.php files
		// remove the directory and it's content
		@rmdir($this->DIR);
		// now rebuild the directory
		mkdir($this->DIR);
		@file_put_contents($this->DIR . '/.htaccess', 'deny from all');
		@file_put_contents($this->DIR . '', '<?php' . "\n" . 'die(\'Hacking attempt...\');' . "\n" . '?>php');
	}

	/**
	 * 
	 * @param type $key
	 * @return type
	 */
	public function getMetadata($key)
	{
		// not sure on this either
		return;
	}

	/**
	 * this function makes calculating the key hash easier
	 * 
	 * @param string $key
	 * @return string-32
	 */
	protected function makeKey($key) {
		return md5(Settings::UNIQUE_8 . '-SMC-' . strtr($this->_normalize($key), ':/', '-_'));
	}
}