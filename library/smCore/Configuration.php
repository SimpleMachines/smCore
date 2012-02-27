<?php

/**
 * smCore platform
 *
 * @package smCore
 * @license MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
*/

namespace smCore;
use smCore\model\Storage, Settings;

/**
 * Class Configuration allows to (re)load and make available core configuration data.
 * Not applied to modules at this time, although if modules add settings to the same table, they're used too.
 *
 */
class Configuration
{
	private $_settings = array();
	private static $_instance = null;

	/**
	 * A Configuration instance will hold the settings.
	 */
	protected function __construct()
	{
		//
	}

	/**
	 * Load the configuration, from cache or database storage. Instantiates a Configuration to hold the settings.
	 *
	 * @static
	 */
	static function loadConfiguration()
	{
		// Instantiate the Configuration.
		if (self::$_instance === null)
			self::$_instance = new Configuration();

		// load application configuration data from the storage.
		// (caching is in Storage)
		self::$_instance->_settings = Storage::getConfigStorage()->readConfig();
	}

	/**
	 * Retrieve a particular setting, if defined.
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	function __get($name)
	{
		if (array_key_exists($name, $this->_settings))
		{
			return $this->_settings[$name];
		}

		return null;
	}

	/**
	 * Set/reset a particular setting.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	function __set($name, $value)
	{
		// alphanumeric string or int
		$this->_settings[$name] = $value;
	}

	/**
	 * Whether a particular setting has a value set.
	 *
	 * @param string $name
	 * @return bool
	 */
	function __isset($name)
	{
		return isset($this->_settings[$name]);
	}

	/**
	 * Unset a particular setting.
	 *
	 * @param string $name
	 */
	function __unset($name)
	{
		unset($this->_settings[$name]);
	}

	/**
	 * Retrieve the Configuration instance.
	 *
	 * @static
	 * @return \smCore\Configuration
	 */
	static function getConf()
	{
		if (self::$_instance === null)
			self::loadConfiguration();
		return self::$_instance;
	}

	/**
	 * Convenience method for a widely used setting, cookie name
	 *
	 * @return string|null
	 */
	function getCookieName()
	{
		return Settings::COOKIE_NAME;
	}

	/**
	 * Convenience method for a widely used setting, local cookies
	 *
	 * @return int
	 */
	function getLocalCookies()
	{
		if (array_key_exists('localCookies', $this->_settings))
		{
			return $this->_settings['localCookies'];
		}

		return 0;
	}

	/**
	 * Convenience method for a widely used setting, global cookies
	 *
	 * @return int
	 */
	function getGlobalCookies()
	{
		if (array_key_exists('globalCookies', $this->_settings))
		{
			return $this->_settings['globalCookies'];
		}

		return 0;
	}
}
