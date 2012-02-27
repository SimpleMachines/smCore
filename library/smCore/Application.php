<?php

/**
 * smCore platform
 *
 * @package smCore
 * @author Fustrate
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
 *
 */

namespace smCore;

use smCore\Request, smCore\Url, smCore\ModuleRegistry, smCore\DefaultAutoloader, smCore\Language,
    smCore\model\User, smCore\AccessManager, smCore\Router, smCore\API, smCore\events\EventDispatcher,
    smCore\model\Storage, smCore\security\Session, smCore\views\ViewManager, \Settings;

/**
 * This is the bootstrap class of the platform.
 * It takes care of loading the absolutely needed, and then it delegates control to
 * the specific action handler. The action handler could be a controller of a module,
 * or a handler of an external API call. (at this time)
 *
 */
class Application
{
	/**
	 *Singleton pattern instance
	 */
	private static $_instance = null;
	/**
	 * The application boots only once.
	 * @var bool=false
	 */
	private static $_hasBooted = false;
	// remove the registry.
	private static $_registry = array();

	private static $_cache = null;
	private $_request = null;
	private $_response = null;
	public $_dispatcher = null;

	public $_context = array();

	public $_time = 0;
	private static $_start_time = 0;

	private function __clone(){}

	/**
	 * @static
	 * @return \smCore\Application
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * Create this instance.
	 */
	protected function __construct()
	{
		require_once(dirname(dirname(dirname(__FILE__))) . '/Settings.php');
	}

	/**
	 * Load all the needed and put things in motion.
	 */
	public final function boot()
	{
		// check if we've been called before. Shouldn't happen.
		if (self::$_hasBooted)
			return;

		self::$_start_time = microtime(true);
		date_default_timezone_set(Settings::APP_TIMEZONE);
		$this->_time = time();

		// @todo this is off for debugging purposes
		// ob_start()

		self::$_hasBooted = true;

		// Register things... autoloader
		include_once(__DIR__ . '/DefaultAutoloader.php');
		DefaultAutoloader::register();

		// Error handler...
		set_error_handler(array('\\smCore\\handlers\\DefaultErrorHandler', 'errorHandler'));
		error_reporting(E_ALL | E_STRICT);

		// Exception handler
		set_exception_handler(array('\\smCore\\handlers\\DefaultExceptionHandler', 'exceptionHandler'));

		// Initialize database connection
		Storage::initConnection(Settings::$database);

		// No access to database details after this point
		Settings::$database = null;

		// Start session!
		Session::startSession();

		// Initialize modules registry
		ModuleRegistry::recompile();

		// Load language...
		Language::getDefaultLanguage();
		$user = User::getCurrentUser();
		if ($user->get('language') !== 'english_us')
			Language::getUserLanguage($user->get('language'));

		// We direct to the appropriate handler for APIs and others, here.
		// (i.e. appropriate output format, whatnot, for RSS/etc).
		// API::run();
		// die()

		// Start up the events subsystem... Ugly, and necessary at all?
		// EventDispatcher::getInstance();

		// Setup view environment...
		ViewManager::getInstance()->setupTheme();

		// Initialize menu
		Menu::setupMenu($this->_context, Router::getRoutes(), true);

		// Find the route and dispatch
		Router::dispatch();

		// this is a beauty.
		ViewManager::theme()->output();
	}

	/**
	 * Add to context array an additional array.
	 *
	 * @param $toAdd
	 */
	function addToContext($toAdd)
	{
		// @todo check toAdd and merge it as appropriate.
		$this->_context += $toAdd;
	}

	/**
	 * Add to context array.
     * Convenience method for adding a (key, value) pair to the $context array
     * to be sent to the template.
     * This should probably move from Application class.
	 *
	 * @param $key
	 * @param $value
	 */
	public static function addValueToContext($key, $value)
	{
		self::getInstance()->_context[$key] = $value;
	}

	/**
	 * Contextual array...
	 *
	 * @return array
	 */
	public function getContext()
	{
		return $this->_context;
	}

	/**
	 * Get start time
	 *
	 * @return int
	 */
	static function getStartTime()
	{
		return self::$_start_time;
	}

	static function set($key, $value)
	{
		if ($value === null)
			unset(self::$_registry[$key]);
		else
			self::$_registry[$key] = $value;
	}

	static function get($key)
	{
		if (array_key_exists($key, self::$_registry))
		{
			return self::$_registry[$key];
		}
		else
		{
			die('bad key throw exception!');
		}
	}
}