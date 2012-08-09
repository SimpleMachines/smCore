<?php

/**
 * smCore Application Class
 *
 * This is the bootstrap class of the platform. It takes care of loading what is absolutely
 * needed, and then it delegates control to the specific action handler. The action handler
 * could be a controller of a module, or a handler of an external API call, at this time.
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

use smCore\Storage, smCore\Handlers, smCore\Cache, smCore\Db;
use Twig_Autoloader, Twig_Environment, Twig_Loader_Filesystem;
use Inspekt, Inspekt_Cage;

class Application
{
	public static $start_time = null;

	protected static $_registry = array();
	protected static $_lazyLoads = array();

	protected function __clone(){}

	public function __construct($settings, $environment = 'default')
	{
		require_once $settings;
		new Settings($environment);
	}

	/**
	 * The main function, runs ALL the things!
	 */
	public function run()
	{
		if (self::$start_time !== null)
		{
			throw new Exception('Cannot load the application again!');
		}

		self::$start_time = microtime(true);
		date_default_timezone_set(Settings::TIMEZONE);
		self::set('time', time());

		// Register our autoloader onto the stack
		require __DIR__ . '/Autoloader.php';
		new Autoloader(null, dirname(__DIR__));

		new Handlers\Error();
		new Handlers\Exception();

		self::addLazyLoader('db', array($this, '_loadDatabase'));
		self::addLazyLoader('cache', array($this, '_loadCache'));
		self::addLazyLoader('mail', array($this, '_loadMail'));
		self::addLazyLoader('theme', array($this, '_loadTheme'));

		self::set('input', Inspekt::makeSuperCage(null, false));

		$request = self::set('request', new Request);
		$response = self::set('response', new Response);

		$dispatcher = self::set('events', new EventDispatcher());
		$dispatcher->setListeners(Storage\Factory::factory('Events')->getActiveListeners());

		$user = self::set('user', Storage\Factory::factory('Users')->getCurrentUser());

		self::set('modules', Storage\Factory::factory('Modules'));
		$lang = self::set('lang', Storage\Factory::factory('Languages')->getByCode($user['language']));
		$lang->loadPackageByName('org.smcore.common');

		self::set('menu', new Menu());

		// @todo don't just call this here
		self::get('theme');

		$dispatcher->fire(new Event(null, 'org.smcore.core.pre_router'));

		$router = new Router();
		$route = $router->match(self::get('request')->getPath());
		self::set('router', $router);

		if (is_int($route))
		{
			// @todo: show the correct error screen
			$response
				->addHeader($route)
				->setBody(self::get('twig')->render('error.html', array(
					'code' => $route,
					'error_message' => $lang->get('exceptions.error_code_' . $route) ?: $lang->get('exceptions.error_code_unknown'),
				)))
			;
		}
		else
		{
			$module = self::get('modules')->getModule($route['module'], $this);

			$returned = $module->runControllerMethod($route['controller'], $route['method']);
			$response->setBody($returned);
		}

		$dispatcher->fire(new Event(null, 'org.smcore.core.post_router'));

		$response->sendOutput();
	}

	/**
	 * Set an internal registry value.
	 *
	 * @param string $key   The name of this value
	 * @param mixed  $value The value to store. Passing null will unset the key from the registry.
	 */
	public static function set($key, $value)
	{
		if ($value === null)
		{
			unset(self::$_registry[$key]);
		}
		else
		{
			self::$_registry[$key] = $value;

			return self::$_registry[$key];
		}
	}

	/**
	 * Get a value from the internal application registry.
	 *
	 * @param string  $key      The name of the value to look for
	 * @param boolean $lazyload Whether we should try lazy loading or not, if the key isn't already set.
	 *
	 * @return mixed Returns the value matched with the key passed, or null if nothing was found.
	 */
	public static function get($key, $lazyload = true)
	{
		if (array_key_exists($key, self::$_registry))
		{
			return self::$_registry[$key];
		}
		else if ($lazyload && array_key_exists($key, self::$_lazyLoads))
		{
			self::set($key, call_user_func_array(self::$_lazyLoads[$key][0], self::$_lazyLoads[$key][1]));
			return self::$_registry[$key];
		}

		return null;
	}

	/**
	 * Add a lazy loader for the internal registry.
	 *
	 * @param string   $key       The key to store the result of the callback under
	 * @param callback $callback  A callback to run
	 * @param array    $arguments Arguments to pass to the callback
	 *
	 * @return 
	 */
	public function addLazyLoader($key, $callback, array $arguments = array())
	{
		// @todo: throw an exception for invalid/duplicate/late
		if (empty($key) || array_key_exists($key, self::$_registry) || array_key_exists($key, self::$_lazyLoads))
		{
			return;
		}

		// @todo: invalid callback exception
		if (!is_callable($callback))
		{
			return;
		}

		self::$_lazyLoads[$key] = array($callback, $arguments);
	}

	/**
	 * Remove a lazy loader.
	 *
	 * @param string $key The name of the lazy loader to remove.
	 */
	public function removeLazyLoader($key)
	{
		if (array_key_exists($key, self::$_lazyLoads))
		{
			unset(self::$_lazyLoads[$key]);
		}
	}

	/**
	 * Only load and connect to the database when necessary.
	 *
	 * @return object The database object created.
	 */
	protected function _loadDatabase()
	{
		$db = Db\Driver\Factory::factory(Settings::$database['driver'], Settings::$database);

		// No access to database details after this point
		Settings::$database = null;

		return $db->getConnection();
	}

	/**
	 * Load the cache when necessary. Lazy loaded upon first call to Application::get('cache')
	 *
	 * @return object A new Cache object to use.
	 */
	protected function _loadCache()
	{
		if (!array_key_exists('prefix', Settings::$cache))
		{
			Settings::$cache['prefix'] = Settings::UNIQUE_8;
		}

		return Cache\Factory::factory(Settings::$cache['driver'], Settings::$cache);
	}

	/**
	 * 
	 *
	 * @return 
	 */
	protected function _loadTheme()
	{
		$user = self::get('user');
		$id = $user['theme'];
		$themes = Storage\Factory::factory('Themes');

		try
		{
			$theme = $themes->getById($user['theme']);
		}
		catch (Exception $e)
		{
			if (Settings::DEFAULT_THEME !== $id)
			{
				$theme = $themes->getById(Settings::DEFAULT_THEME);
			}
		}

		Twig_Autoloader::register();

		$twig_loader = new Twig_Loader_Filesystem(Settings::THEME_DIR . '/' . $theme->getDirectory());

		$twig = self::set('twig', new Twig_Environment($twig_loader, array(
			'cache' => Settings::CACHE_DIR,
			'recompile' => true,
			'auto_reload' => true,
		)));

		$twig->addExtension(new TwigExtension());
		$twig->addGlobal('scripturl', Settings::URL);
		$twig->addGlobal('theme_url', trim(Settings::URL, '/?') . '/themes/' . $theme->getDirectory());
		$twig->addGlobal('default_theme_url', trim(Settings::URL, '/?') . '/themes/default');
		$twig->addGlobal('reload_counter', 0); // @todo

		return $theme;
	}

	/**
	 * Setup for the mail object
	 */
	protected function _loadMail()
	{
	}
}
