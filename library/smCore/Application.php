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
	protected $_container;

	public static $start_time = null;

	protected function __clone(){}

	public function __construct(Settings $settings)
	{
		$this->_container = new Container;
		$this->_container['settings'] = $settings;
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

		$this->_container['storage_factory'] = new Storage\Factory($this->_container);

		new Handlers\Error($this->_container);
		new Handlers\Exception($this->_container);
		new Handlers\Session($this->_container);

		$this->_container['session'] = new Security\Session($this->_container);

		$this->_container['db'] = array($this, 'loadDatabase');
		$this->_container['cache'] = array($this, 'loadCache');
		$this->_container['mail'] = array($this, 'loadMail');
		$this->_container['twig'] = array($this, 'loadTheme');

		$this->_container['input'] = Inspekt::makeSuperCage(null, false);

		$this->_container['request'] = new Request($this->_container);
		$this->_container['response'] = new Response($this->_container);

		$this->_container['events'] = new EventDispatcher;
		$this->_container['events']->setListeners($this->_container['storage_factory']->factory('Events')->getActiveListeners());

		$this->_container['user'] = $this->_container['storage_factory']->factory('Users')->getCurrentUser();

		$this->_container['modules'] = $this->_container['storage_factory']->factory('Modules');
		$this->_container['lang'] = $this->_container['lang'] = $this->_container['storage_factory']->factory('Languages')->getByCode($this->_container['user']['language']);
		$this->_container['lang']->loadPackageByName('org.smcore.common');

		$this->_container['menu'] = new Menu($this->_container);

		// @todo don't just call this here
		$theme = $this->_container['theme'];

		$this->_container['events']->fire(new Event(null, 'org.smcore.core.pre_router'));

		$this->_container['router'] = new Router;
		$this->_container['router']
			->addRoutes(array(
				'(?:themes|resources).*' => 404,
				'(?:cache|library).*?' => 403,
			), 'smCore')
			->setDefaultRoute('hello')
		;

		foreach ($this->_container['modules'] as $identifier => $module)
		{
			$this->_container['router']->addRoutes($module->getRoutes(), $identifier);
		}

		$route = $this->_container['router']->match($this->_container['request']->getPath());

		if (is_int($route['method']))
		{
			$code = $route['method'];

			// @todo: show the correct error screen
			$this->_container['response']
				->addHeader($code)
				->setBody($this->_container['twig']->render('error.html', array(
					'code' => $code,
					'error_message' => $this->_container['lang']->get('exceptions.error_code_' . $code) ?: $this->_container['lang']->get('exceptions.error_code_unknown'),
				)))
			;
		}
		else
		{
			$module = $this->_container['modules']->getModule($route['module'], $this);

			$this->_container['response']->setBody($module->runControllerMethod($route['controller'], $route['method']));
		}

		$this->_container['events']->fire(new Event(null, 'org.smcore.core.post_router'));

		$this->_container['response']->sendOutput();
	}

	/**
	 * Only load and connect to the database when necessary.
	 *
	 * @return object The database object created.
	 */
	public function loadDatabase()
	{
		$db = Db\Driver\Factory::factory($this->_container['settings']['database']['driver'], $this->_container['settings']['database']);

		return $db->getConnection();
	}

	/**
	 * Load the cache when necessary. Lazy loaded by the dependency injection container.
	 *
	 * @return object A new Cache object to use.
	 */
	public function loadCache()
	{
		return Cache\Factory::factory($this->_container['settings']['cache']['driver'], $this->_container['settings']['cache']);
	}

	/**
	 * 
	 *
	 * @return Twig_Environment
	 */
	public function loadTheme()
	{
		$user = $this->_container['user'];
		$id = $user['theme'];
		$themes = $this->_container['storage_factory']->factory('Themes');
		$settings = $this->_container['settings'];

		try
		{
			$theme = $themes->getById($user['theme']);
		}
		catch (Exception $e)
		{
			if ($settings['default_theme'] !== $id)
			{
				$theme = $themes->getById($settings['default_theme']);
			}
			else
			{
				// @todo: throw exception
			}
		}

		Twig_Autoloader::register();

		$twig_loader = new Twig_Loader_Filesystem($settings['theme_dir'] . '/' . $theme->getDirectory());
		$twig_loader->addPath($settings['theme_dir'] . '/' . $theme->getDirectory(), 'theme');

		$twig = new Twig\Environment($this->_container, $twig_loader, array(
			'cache' => $settings['cache_dir'],
			'recompile' => true,
			'auto_reload' => true,
		));

		$twig->addExtension(new Twig\Extension($this->_container));

		// @todo: only enable this extension if we're in debug mode
//		$twig->addExtension(new \Twig_Extension_Debug());

		return $twig;
	}

	/**
	 * Setup for the mail object
	 */
	public function loadMail()
	{
	}
}