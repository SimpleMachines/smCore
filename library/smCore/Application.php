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

use Twig_Autoloader, Twig_Environment, Twig_Loader_Filesystem;
use Inspekt, Inspekt_Cage;

class Application extends Container
{
	const VERSION = '1.0 Alpha 1';

	protected function __clone(){}

	/**
	 * Creates a new smCore application
	 *
	 * @param \smCore\Settings $settings A settings object to grab vital information from
	 */
	public function __construct(Settings $settings)
	{
		$this['settings'] = $settings;
	}

	/**
	 * The main function, runs ALL the things!
	 */
	public function run()
	{
		if (null !== $this['start_time'])
		{
			throw new Exception('Cannot load the application again!');
		}

		$this['start_time'] = microtime(true);

		$this['storage_factory'] = new Storage\Factory($this);

		new Handlers\Error($this);
		new Handlers\Exception($this);
		new Handlers\Session($this);

		$this['session'] = new Security\Session($this);

		$this->add('db', array($this, 'loadDatabase'));
		$this->add('cache', array($this, 'loadCache'));
		$this->add('mail', array($this, 'loadMail'));
		$this->add('twig', array($this, 'loadTheme'));

		$this['input'] = Inspekt::makeSuperCage(null, false);

		$this['request'] = new Request($this);
		$this['response'] = new Response($this);

		$this['events'] = new EventDispatcher;
		$this['events']->addListeners($this['storage_factory']->factory('Events')->getActiveListeners());

		$this['user'] = $this['storage_factory']->factory('Users')->getCurrentUser();

		$this['modules'] = $this['storage_factory']->factory('Modules');

		$this['lang'] = $this['lang'] = $this['storage_factory']->factory('Languages')->getByCode($this['user']['language']);
		$this['lang']->loadPackageByName('org.smcore.common');

		$this['menu'] = new Menu($this);

		// @todo don't just call this here
		$theme = $this['theme'];

		$this['events']->fire('org.smcore.core.pre_router');

		$this['router'] = new Router;
		$this['router']
			->addRoutes(array(
				'(?:themes|resources).*' => 404,
				'(?:cache|library).*?' => 403,
			), 'smCore')
			->setDefaultRoute('hello')
		;

		foreach ($this['modules'] as $identifier => $module)
		{
			$this['router']->addRoutes($module->getRoutes(), $identifier);
		}

		$route = $this['router']->match($this['request']->getPath());

		if (is_int($route['method']))
		{
			$code = $route['method'];

			// @todo: show the correct error screen
			$this['response']
				->addHeader($code)
				->setBody($this['twig']->render('error.html', array(
					'code' => $code,
					'error_message' => $this['lang']->get('exceptions.error_code_' . $code) ?: $this['lang']->get('exceptions.error_code_unknown'),
				)))
			;
		}
		else
		{
			$module = $this['modules']->getModule($route['module'], $this);

			$this['response']->setBody($module->runControllerMethod($route['controller'], $route['method']));
		}

		$this['events']->fire('org.smcore.core.post_router');

		$this['response']->sendOutput();
	}

	/**
	 * Only load and connect to the database when necessary.
	 *
	 * @return object The database object created.
	 */
	public function loadDatabase()
	{
		$db = Db\Driver\Factory::factory($this['settings']['database']['driver'], $this['settings']['database']);

		return $db->getConnection();
	}

	/**
	 * Load the cache when necessary. Lazy loaded by the dependency injection container.
	 *
	 * @return object A new Cache object to use.
	 */
	public function loadCache()
	{
		return Cache\Factory::factory($this['settings']['cache']['driver'], $this['settings']['cache']);
	}

	/**
	 * Load Twig and everything that goes along with it
	 *
	 * @return Twig_Environment
	 */
	public function loadTheme()
	{
		$user = $this['user'];
		$id = $user['theme'];
		$themes = $this['storage_factory']->factory('Themes');
		$settings = $this['settings'];

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

		$twig = new Twig\Environment($this, $twig_loader, array(
			'cache' => $settings['cache_dir'],
			'recompile' => true,
			'auto_reload' => true,
		));

		$twig->addExtension(new Twig\Extension($this));

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