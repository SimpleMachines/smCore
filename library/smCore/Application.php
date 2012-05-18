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
use smCore\Event\Dispatcher as EventDispatcher, smCore\Storage\Factory as StorageFactory,
	smCore\Handlers\Error as ErrorHandler, smCore\Handlers\Exception as ExceptionHandler,
	Zend_Db, Zend_Db_Table_Abstract, Zend_Cache, Zend_Mail,
	Inspekt, Inspekt_Cage;

class Application
{
	// Only allow one call to the run() function
	private static $_run = false;

	public static $haste;

	public static $context = array();

	public static $start_time = null;

	protected static $_registry = array();
	protected static $_lazyLoads = array();

	protected function __clone(){}

	public function __construct()
	{
		require_once(dirname(dirname(__DIR__)) . '/settings.php');
	}

	/**
	 * The main function, runs ALL the things!
	 */
	public function run()
	{
		if (self::$_run)
			throw new Exception('Cannot load the application again!');

		self::$_run = true;

		self::$start_time = microtime(true);
		date_default_timezone_set(Settings::TIMEZONE);
		self::set('time', time());

		// Register our autoloader onto the stack
		require __DIR__ . '/Autoloader.php';
		Autoloader::register();

		new ErrorHandler();
		new ExceptionHandler();

		self::addLazyLoader('db', array($this, '_loadDatabase'));
		self::addLazyLoader('cache', array($this, '_loadCache'));
		self::addLazyLoader('mail', array($this, '_loadMail'));
		self::addLazyLoader('theme', array($this, '_loadTheme'));

		self::set('input', Inspekt::makeSuperCage(null, false));
		self::set('request', new Request);
		self::set('response', new Response);

		$user = StorageFactory::getStorage('Users')->getCurrentUser();
		self::set('user', $user);

		self::set('modules', StorageFactory::getStorage('Modules'));
		self::set('event_dispatcher', new EventDispatcher());

		$lang = new Language($user['language']);
		$lang->load(Settings::LANGUAGE_DIR . '/core.yaml');
		self::set('lang', $lang);

		$this->_setupTheme();

		$router = new Router();
		$route = $router->match(self::get('request')->getPath());
		self::set('router', $router);

		if ($route === false)
		{
			self::$haste
				->addGlobal('page_title', '404')
				->addView('error_404.tpl');
		}
		else
		{
			$module = self::get('modules')->getModule($route['module']);

			$module->loadController($route['controller']);
			$module->runControllerMethod($route['method']);
		}

		$post_router_event = new Event(null, 'org.smcore.core.post_router');
		$post_router_event->fire();

		if (!isset(self::$context['uses_wysiwyg']))
			self::$context['uses_wysiwyg'] = false;

		if (!isset(self::$context['requires_js']))
			self::$context['requires_js'] = false;

		self::$context['requires_js'] |= self::$context['uses_wysiwyg'];

		self::$haste
			->addGlobal('menu', self::get('menu')->getMenu())
			->addGlobal('requires_js', self::$context['requires_js'] || self::$context['uses_wysiwyg'])
			->display();
	}

	// @todo: put this in its own file, a theme storage
	protected function _setupTheme()
	{
		$cache = Application::get('cache');
		$user = Application::get('user');

		$id = $user['theme'];

		if (($theme = $cache->load('theme_' . $id)) === false)
		{
			$db = self::get('db');

			$result = $db->query("
				SELECT *
				FROM beta_themes
				WHERE id_theme = ?", array($id));

			// If the user's theme doesn't exist, try the default theme instead
			if ($result->rowCount() < 1 && $id != 1)
				$result = $db->query("
					SELECT *
					FROM beta_themes
					WHERE id_theme = 1");

			if ($result->rowCount() < 1)
				throw new Exception('exceptions.themes.no_default');

			$theme = $result->fetch();
			$cache->save($theme, 'theme_' . $id);
		}
/*
$loader = new \Twig_Loader_Filesystem(Settings::THEME_DIR . '/' . $theme->theme_dir);
$twig = new \Twig_Environment($loader, array(
	'cache' => false,
));

$twig->render('index.tpl', array(
));

die('got here');

return;
*/
		\Twig_Autoloader::register();
		$Twig_theme = \Twig_Environment::loadTheme(Settings::THEME_DIR . '/' . $theme->theme_dir . '/include.php', 'haste' . $theme->theme_class);

		self::$haste = new \Twig_Environment($Twig_theme, array(
			'cache' => Settings::CACHE_DIR,
			'recompile' => true,
			'auto_reload' => true,
		));

		self::get('lang')->load(Settings::LANGUAGE_DIR . '/menu.yaml');

		self::$context += array(
			'page_title' => '...',
			'reload_counter' => 0,
			'theme_url' => trim(Settings::URL, '/?') . '/themes/' . $theme->theme_dir,
			'default_theme_url' => trim(Settings::URL, '/?') . '/themes/default',
			'scripturl' => Settings::URL,
			'time_display' => date('g:i:s A', time()),
			'uses_wysiwyg' => false,
			'requires_js' => false,
			'hide_sidebar' => false,
		);
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
			unset(self::$_registry[$key]);
		else
			self::$_registry[$key] = $value;
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
			return;

		// @todo: invalid callback exception
		if (!is_callable($callback))
			return;

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
			unset(self::$_lazyLoads[$key]);
	}

	/**
	 * Only load and connect to the database when necessary.
	 *
	 * @return Zend_Db The database object created.
	 */
	protected function _loadDatabase()
	{
		$db = Zend_Db::factory(Settings::$database['adapter'], Settings::$database);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);
		$db->setFetchMode(Zend_Db::FETCH_OBJ);

		// No access to database details after this point
		Settings::$database = null;

		return $db;
	}

	/**
	 * Load the cache when necessary. Lazy loaded upon first call to Application::get('cache')
	 *
	 * @todo Read real cache settings from Settings, use the smCore\Cache class instead
	 *
	 * @return Zend_Cache A new Zend Cache object to use.
	 */
	protected function _loadCache()
	{
		return Zend_Cache::factory(
			'Core',
			'File',
			array(
				'lifetime' => 7200,
				'automatic_serialization' => true,
			),
			array(
				'cache_dir' => Settings::CACHE_DIR,
			)
		);
	}

	/**
	 * 
	 *
	 * @return 
	 */
	protected function _loadTheme()
	{
	}

	/**
	 * Setup for the mail object
	 */
	protected function _loadMail()
	{
		Zend_Mail::setDefaultFrom(Settings::MAIL_FROM, Settings::MAIL_FROM_NAME);
	}
}