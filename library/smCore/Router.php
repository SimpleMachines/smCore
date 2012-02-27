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
use smCore\Application, smCore\ModuleRegistry, smCore\model\User,
	Zend_Session_Namespace;

/**
 * Router class. This centralizes known routes and is able to compile, find, process registered routes.
 */
class Router
{
	protected static $_routes = null;

	// Controllers cascade down, so if we find one before we find a method, we keep it here.
	static $module = null;
	static $controller = null;
	static $method = null;

	static $_redirect_to_login = false;

	final private function __clone(){}

	/**
	 * Get all known routes. Recompiles the known modules routes if needed.
	 *
	 * @static
	 * @return array
	 */
	public static function getRoutes()
	{
		// @todo load from cache
        self::recompile();

		return self::$_routes;
	}

	/**
	 * Recompiles known modules and other sources (perhaps), to rebuild the array of routes.
	 *
	 * @static
	 */
	public static function recompile()
	{
		self::$_routes = array();
		$modules = ModuleRegistry::getIdentifiers();

		foreach ($modules as $identifier)
		{
			$config = ModuleRegistry::getModuleConfig($identifier);

			// Doesn't have any routes… which is weird.
			if (empty($config['routes']))
				continue;

			self::_addRoutes(self::$_routes, $config['routes'], $identifier);
		}
	}

    /**
     * Dispatch execution to the appropriate action handler.
     *
     * @static
     * @throws Exception
     */
	public static function dispatch()
	{
		Router::findRoute();

		try
		{
			$controller = new self::$controller;
		}
		catch (Exception $e)
		{
			throw new Exception('core_route_not_callable');
		}

		if (!is_callable(array($controller, self::$method)))
			throw new Exception('core_route_not_callable');

		$controller->setParentModule(ModuleRegistry::getInstance()->getModule(self::$module));
		$controller->preDispatch();
		$controller->{self::$method}();
		$controller->postDispatch();
	}

    /**
     * @static
     *
     */
	static function setup()
	{
		self::getRoutes();
		self::_searchRoutes(self::$_routes);
	}

    /**
     * @static
     * @throws Exception
     */
	static function run()
	{
		if (self::$_redirect_to_login)
			Request::getInstance()->redirect('login/');

		// We wait until now to error out because the theme wasn't loaded when setup() was run
		// self::$_module will be set if self::$_controller is set, no need to test for it
		if (self::$controller === null || self::$method === null)
		{
			throw new Exception('exceptions.router.not_allowed');
		}

		$module = ModuleRegistry::getInstance()->getModule(self::$module);
		$module->loadController(self::$controller);

		Application::set('module', $module);

		$module->runControllerMethod(self::$method);
	}

	/**
	 * Find the appropriate route for the current URL...
	 * It gets the URL from Application object. It should actually receive the URL from somewhere, instead.
	 * Moreover, it attempts to process all routes, breaking when it finds an appropriate one. This is fine,
	 * however look into implementing a chain of responsibility.
	 *
	 * @static
	 * @throws Exception
	 */
	static function findRoute()
	{
		$test = self::getRoutes();
		self::_searchRoutes(self::getRoutes());

		if (self::$controller === null || self::$method === null)
		{
			die('404');
		}
	}

	/**
	 * Search the available routes.
	 *
	 * @static
	 * @param $routes
	 * @return mixed
	 */
	protected static function _searchRoutes($routes)
	{
		if (empty($routes))
			return;

		$key = Request::getUrl()->getNext();

		if ($key === null)
		{
			// Find a default route
			foreach ($routes as $url => $route)
			{
				if (!empty($route['default']) && self::_isAccessible($route))
				{
					self::_processRoute($routes[$url]);
					break;
				}
			}
		}
		else if (array_key_exists($key, $routes))
		{
			// Found a route!
			if (self::_checkPermission($routes[$key]))
				self::_processRoute($routes[$key]);
		}
		else if (array_key_exists('*', $routes))
		{
			// Got caught by the catch-all!
			if (self::_checkPermission($routes['*']))
			{
				if (!empty($routes['*']['saveAs']))
				{
					$_GET[$routes['*']['saveAs']] = $key;
					Request::getUrl()->rebuildCages();
				}

				self::_processRoute($routes['*']);
			}
		}
	}

	/**
	 * Process the given route, to find an appropriate controller and method to run.
	 *
	 * @static
	 * @param $route
	 * @return mixed
	 */
	protected static function _processRoute(&$route)
	{
		if (!empty($route['controller']))
			self::$controller = $route['controller'];

		if (!empty($route['module']))
			self::$module = $route['module'];

		$route['active'] = true;

		// If there's a next part to go to, or there's no controller or method, try going deeper
		if (Request::getUrl()->hasNext() || empty($route['method']) || empty(self::$controller))
		{
			if (!empty($route['routes']))
				self::_searchRoutes($route['routes']);
			else
				return;
		}
		else
			self::$method = $route['method'];
	}

	/**
	 * Check permission for the route, of the current user. Never returns anything, instead it redirects
	 * to login if suitable, or throws an Exception.
	 *
	 * @static
	 * @param $route
	 * @return bool
	 */
	protected static function _checkPermission($route)
	{
		if (!self::_isAccessible($route))
		{
			// The route exists, you might have permissions when you're logged in... but you're not.
			if (User::getCurrentUser()->hasRole('guest'))
			{
				// Maybe they just don't have the permission because they're not logged in.
				// Store this URL in the session so we can redirect to it after login.
				\smCore\security\Session::save('login_url');
				$session = new Zend_Session_Namespace('Core');
				$url = Request::getUrl()->getRawUrl();

				// Don't redirect to anything that ends in "logout". Bad idea for trying to log back in.
				if (!preg_match('~/logout/?~', $url))
					$session->login_redirect = $url;
				else if (isset($session->login_redirect))
					unset($session->login_redirect);

				self::$_redirect_to_login = true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Add an array of routes to an existing parent.
	 *
	 * @static
	 * @param $parent
	 * @param $routes
	 * @param $identifier
	 */
	protected static function _addRoutes(&$parent, $routes, $identifier)
	{
		foreach ($routes as $key => $route)
		{
			if (!empty($parent[$key]))
			{
				// Pre-existing, so we need to be careful. Don't overwrite any existing keys!
				foreach ($route as $data_key => $data)
					if ($data_key !== 'routes' && empty($parent[$key][$data_key]))
						$parent[$key][$data_key] = $data;

				if (!empty($route['controller']) && empty($parent[$key]['module']))
					$parent[$key]['module'] = $identifier;

				// More routes to do? Only necessary in careful mode.
				if (!empty($route['routes']))
					self::_addRoutes($parent[$key]['routes'], $route['routes'], $identifier);
			}
			else
			{
				$parent[$key] = $route;
				$parent[$key]['module'] = $identifier;
			}
		}
	}

	/**
	 * Returns whether the current user has access to the respective route.
	 *
	 * @static
	 * @param $route
	 * @return bool
	 * @throws Exception
	 */
	protected static function _isAccessible($route)
	{
		if (!array_key_exists('access', $route))
			return true;

		if ($route['access'] === false)
			return false;

		if (!empty($route['access']['resource']))
		{
			if (!User::getCurrentUser()->isAllowed($route['access']['resource'][0], $route['access']['resource'][1]))
				return false;
		}

		if (!empty($route['access']['callback']))
		{
			if (!is_callable($route['access']['callback']))
				throw new Exception('exceptions.router.access_callback_invalid');

			if (call_user_func($route['access']['callback']) !== true)
				return false;
		}

		return true;
	}

	/**
	 * Instance of Router. (yes, overused.)
	 *
	 * @static
	 * @return Router
	 */
	final public static function getInstance()
	{
		if (empty($instance))
			$instance = new Router();

		return $instance;
	}
}