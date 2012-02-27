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
 * @version 1.0 alpha
 */

namespace smCore\model;

use database\DatabaseFactory, smCore\Exception,
	smCore\storage\ConfigStorage, smCore\storage\ThemeStorage, smCore\storage\SessionStorage,
	smCore\storage\MemberStorage;

/**
 * Storage utility. Handles database layer connections and will act like a factory (of sorts) to
 * create and return the appropriate Storage class when requested.
 */
class Storage
{
	// these will be factored into adapters, but for a few let it be
	// all getXxxStorage() are only useful to have type of Storage class available during development.
	// they're all disappear in this form.
	private static $_sessionStorage = null;
	private static $_themeStorage = null;
	private static $_configStorage = null;
	private static $_permissionStorage = null;
	private static $_memberStorage = null;

	// this is a simplification - database connections are multiple and the layer switching has to be considered here.
	private static $_defaultConn = null;

	/**
	 * Retrieve a database connection. This is a simplified method for convenience.
	 * @todo handle multiple connections; handle database layers
	 *
	 * @static
	 * @return
	 */
	static function database()
	{
		if(!empty(self::$_defaultConn))
			return self::$_defaultConn;
		throw new Exception ('core_database_not_initialized');
    }

	/**
     * Initialize connection
     *
	 * @param $adapter
	 */
	static function initConnection($adapter)
	{
		// Lets do it the hard way for now.
		if (file_exists($adapter['ssi']))
			include_once($adapter['ssi']);
		self::$_defaultConn = DatabaseFactory::getAdapter($adapter['adapter']);
		try
		{
			$connection = self::$_defaultConn->initiate($adapter['host'], $adapter['dbname'], $adapter['username'], $adapter['password'], $adapter['prefix']);
		}
		catch (Exception $e)
		{
			// Oops, didn't work.
			// @todo
		}
	}

	/**
	 * Creates and/or returns a SessionStorage.
	 *
	 * @static
	 * @return \smCore\storage\SessionStorage
	 */
	static function getSessionStorage()
	{
		if (self::$_sessionStorage === null)
			self::$_sessionStorage = new SessionStorage();
		return self::$_sessionStorage;
	}

	/**
	 * Creates and/or returns a ThemeStorage.
	 *
	 * @static
	 * @return \smCore\storage\ThemeStorage
	 */
	static function getThemeStorage()
	{
		if (self::$_themeStorage === null)
			self::$_themeStorage = new ThemeStorage();
		return self::$_themeStorage;
	}

	/**
	 * Creates and/or returns a ConfigStorage.
	 *
	 * @static
	 * @return \smCore\storage\ConfigStorage
	 */
	static function getConfigStorage()
	{
		if (self::$_configStorage === null)
			self::$_configStorage = new ConfigStorage();
		return self::$_configStorage;
	}

	/**
	 * Creates and/or returns a PermissionStorage.
	 *
	 * @static
	 * @return \smCore\storage\PermissionStorage
	 */
	static function getPermStorage()
	{
		if (self::$_permissionStorage === null)
			self::$_permissionStorage = new \smCore\storage\PermissionStorage();
		return self::$_permissionStorage;
	}

	static function __callStatic($name, $arguments)
	{
		$adapter = self::database();
		// Bit hackish, don't want to this in fact.
		// It becomes irrelevant since callStatic (for $smcFunc) will disappear.
		if (!empty($arguments) && isset($arguments['type']))
		{
			$connection = $adapter->getConnection($arguments['type']);
			unset($arguments['type']);
		}
		else
		{
			$connection = $adapter->getConnection();
		}

		return call_user_func_array(array($adapter, $name), $arguments);
	}

	/**
	 * @static
	 * @return \smCore\storage\MemberStorage
	 */
	static function getMemberStorage()
	{
		if (self::$_memberStorage === null)
			self::$_memberStorage = new MemberStorage();
		return self::$_permissionStorage;
	}

}

