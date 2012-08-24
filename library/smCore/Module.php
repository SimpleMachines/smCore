<?php

/**
 * smCore Module Class
 *
 * With lazy documentation!
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

use smCore\Security\Session;

class Module
{
	protected $_app;

	protected $_template_dir;

	protected $_directory;
	protected $_config;

	protected $_controller = null;
	protected $_storages = array();

	protected $_has_dispatched = false;

	/**
	 * Module constructor. Handles the initial setup.
	 *
	 * @param array  $config    The contents of this module's config.yaml file.
	 * @param string $directory The directory where this module is located.
	 */
	public function __construct(Application $app, $config, $directory)
	{
		$this->_app = $app;

		$this->_config = $config;
		$this->_directory = $directory;

		$this->_template_dir = $this->_directory . '/Views/';

		$this->_config['namespaces'] = array_merge(array(
			'cache' => str_replace('.', '_', $this->_config['identifier']),
			'lang' => $this->_config['identifier'],
			'template' => $this->_config['identifier'],
		), $this->_config['namespaces']);

		if (empty($this->_config['settings']))
		{
			$this->_config['settings'] = array();
		}

		if (is_dir($this->_template_dir))
		{
			$app['twig']->getLoader()->addPath($this->_template_dir, $this->_config['namespaces']['template']);
			$app['twig']->getLoader()->addPath($this->_template_dir, $this->_config['identifier']);
		}
	}

	/**
	 * Dispatch a method under the loaded controller.
	 *
	 * @param string $controller The controller name to load.
	 * @param string $method     The method name to dispatch.
	 */
	public function runControllerMethod($controller, $method)
	{
		if ($this->_has_dispatched)
		{
			throw new Exception('A controller method has already been dispatched.');
		}

		if (!file_exists($this->_directory . '/Controllers/' . $controller . '.php'))
		{
			throw new Exception(array('exceptions.modules.invalid_controller', $controller));
		}

		$controllerClass = $this->_config['namespaces']['php'] . '\\Controllers\\' . $controller;
		$controllerObject = new $controllerClass($this->_app, $this);

		if (!is_callable(array($controllerObject, $method)))
		{
			throw new Exception(array('exceptions.modules.method_not_callable', $controllerClass, $method));
		}

		$controllerObject->preDispatch($method);
		$output = $controllerObject->$method();
		$controllerObject->postDispatch($method);

		$this->_has_dispatched = true;

		return $output;
	}

	/**
	 * Load and return a model in this module's /Models/ directory by name.
	 *
	 * @param string $name The name of the model to load.
	 *
	 * @return smCore\Module\Model
	 */
	public function getModel($name)
	{
		if (!file_exists($this->_directory . '/Models/' . $name . '.php'))
		{
			throw new Exception(array('exceptions.modules.invalid_model', $name));
		}

		$modelClass = $this->_config['namespaces']['php'] . '\\Models\\' . $name;

		return new $modelClass($this->_app, $this);
	}

	/**
	 * Load and return a storage in this module's /Storages/ directory by name.
	 *
	 * @param string $name The name of the storage to load.
	 *
	 * @return smCore\Module\Storage
	 */
	public function getStorage($name)
	{
		if (empty($this->_storages[$name]))
		{
			if (!file_exists($this->_directory . '/Storages/' . $name . '.php'))
			{
				throw new Exception(array('exceptions.modules.invalid_storage', $name));
			}

			$storageClass = $this->_config['namespaces']['php'] . '\\Storages\\' . $name;
			$this->_storages[$name] = new $storageClass($this->_app, $this);
		}

		return $this->_storages[$name];
	}

	/**
	 * Returns the directory where this module is located.
	 *
	 * @return string The directory where this module is located.
	 */
	public function getDirectory()
	{
		return $this->_directory;
	}

	public function render($name, array $context = array(), $sending_output = true)
	{
		if ($sending_output)
		{
			$this->_app['sending_output'] = true;
		}

		return $this->_app['twig']->render('@' . $this->_config['namespaces']['template'] . '/' . $name . '.html', $context);
	}

	public function display($name, array $context = array(), $sending_output = true)
	{
		if ($sending_output)
		{
			$this->_app['sending_output'] = true;
		}

		$this->_app['twig']->display('@' . $this->_config['namespaces']['template'] . '/' . $name . '.html', $context);

		return $this;
	}

	/**
	 * Load a language package
	 *
	 * @param string  $package_name The name of the language package to load, i.e. "common"
	 * @param boolean $force_reload If this is not false, ignore any cached version.
	 */
	public function loadLangPackage($package_name = null, $force_reload = false)
	{
		if (empty($package_name))
		{
			$this->_app['lang']->loadPackageByName($this->_config['identifier']);
		}
		else
		{
			$this->_app['lang']->loadPackageByName($this->_config['identifier'] . '.' . $package_name);
		}

		return $this;
	}

	/**
	 * Lang!
	 *
	 * @param string|array $key
	 * @param array        $replacements
	 *
	 * @return string
	 */
	public function lang($key, array $replacements = array(), $namespace = true)
	{
		return $this->_app['lang']->get(($namespace ? $this->_config['namespaces']['lang'] . '.' : '') . $key, $replacements);
	}

	/**
	 * A shortcut to allow us to throw language strings in exceptions
	 *
	 * @param string|array $key
	 * @param array        $replacements
	 *
	 * @throws \smCore\Exception
	 */
	public function throwLangException($key, array $replacements = array())
	{
		throw new Exception($this->lang('exceptions.' . $key, $replacements));
	}

	/**
	 * Create an event, under this module's namespace, that can be fired/used later.
	 *
	 * @param string $name
	 * @param mixed  $arguments
	 *
	 * @return \smCore\Event
	 */
	public function createEvent($name, $arguments = null)
	{
		// Use proper namespacing - "com.fustrate.calendar" + "load" = "com.fustrate.calendar.load"
		return new Event($this, $this->_config['identifier'] . '.' . $name, $arguments);
	}

	/**
	 * Fire and return an event
	 *
	 * @param string $name      Name of the event
	 * @param mixed  $arguments 
	 *
	 * @return \smCore\Event
	 */
	public function fire($event, array $arguments = array())
	{
		if (!$event instanceof Event)
		{
			if (!is_string($event) || empty($event))
			{
				throw new Exception('Event names must be strings.');
			}

			$event = $this->_config['identifier'] . '.' . $event;
		}

		return $this->_app['events']->fire($event);		
	}

	/**
	 * Check to see if the current user has a certain permission for this module.
	 *
	 * @param string $name The permission name to check under this module's identifier.
	 *
	 * @return boolean
	 */
	public function hasPermission($name)
	{
		return $this->_app['user']->hasPermission($this->_config['identifier'] . '.' . $name);
	}

	/**
	 * Kick a user out if they don't have the correct permissions.
	 *
	 * @param $name The permission to check. It will be prepended by this module's identifier and a period ("edit" -> "org.smcore.yourmodule" . "." . $name)
	 */
	public function requirePermission($name, $use_namespace = true)
	{
		if ($use_namespace)
		{
			$name = $this->_config['identifier'] . '.' . $name;
		}

		if (!$this->_app['user']->hasPermission($name))
		{
			throw new Exception('exceptions.no_permission');
		}

		return $this;
	}

	public function requireAdmin()
	{
		if (!$this->_app['user']->isAdmin())
		{
			if (!$this->_app['user']->isLoggedIn())
			{
				$this->_app['session']->start();
				$_SESSION['redirect_url'] = $this->_app['request']->getUrl();
				$this->_app['response']->redirect('login');
			}

			throw new Exception('exceptions.admin_required');
		}

		return $this;
	}

	public function noGuests($message = null, $exception_on_failure = true)
	{
		if ($this->_app['user']->hasRole(0))
		{
			if ($exception_on_failure)
			{
				throw new Exception($message ?: 'exceptions.no_guest_access');
			}

			return false;
		}

		return true;
	}

	public function validateSession($type, $lifetime = 3600)
	{
		if (!isset($_SESSION['session_' . $type]) || $_SESSION['session_' . $type] + $lifetime < time())
		{
			$input = $this->_app['input'];

			if ($input->post->keyExists('authenticate_pass'))
			{
				$bcrypt = new Security\Crypt\Bcrypt();

				if ($bcrypt->match($input->post->getRaw('authenticate_pass'), $this->_app['user']['password']))
				{
					$_SESSION['session_' . $type] = time();

					if (isset($_SESSION['redirect_url']))
					{
						$url = $_SESSION['redirect_url'];
					}
					else
					{
						$url = 'admin';
					}

					$this->_app['response']->redirect($url);
				}
			}

			if ('admin/authenticate' !== $path = $this->_app['request']->getPath())
			{
				$_SESSION['redirect_url'] = $path;
				$this->_app['response']->redirect('/admin/authenticate/');
			}
		}

		return $this;
	}

	public function endSession($type)
	{
		unset($_SESSION['session_' . $type]);
	}

	/**
	 * Retrieve a module setting by name.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getSetting($name)
	{
		if (isset($this->_config['settings'][$name]))
		{
			return $this->_config['settings'][$name];
		}

		return null;
	}

	public function getRoutes()
	{
		return $this->_config['routes'];
	}

	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 *
	 *
	 * @param string $key      Unique key to save this data under, in the module's namespace.
	 * @param mixed  $data     The value to save to the cache.
	 * @param int    $lifetime Amount of time to keep this data in the cache, optional.
	 */
	public function cacheSave($key, $data, $lifetime = null)
	{
		$this->_app['cache']->save($this->_config['namespaces']['cache'] . '.' . $key, $data, $lifetime);
	}

	/**
	 *
	 *
	 * @param string $key The key to find in the cache.
	 */
	public function cacheLoad($key)
	{
		return $this->_app['cache']->load($this->_config['namespaces']['cache'] . '.' . $key);
	}

	/**
	 *
	 *
	 * @param string $key The key to test for in the cache.
	 */
	public function cacheTest($key)
	{
		return $this->_app['cache']->test($this->_config['namespaces']['cache'] . '_' . $key);
	}

	/**
	 * Create a unique token to use for a (likely destructive) request.
	 *
	 * @param string $name The name of the token
	 *
	 * @return string
	 */
	public function createToken($name)
	{
		return md5(hash('sha256', $name . '%' . $this->_app['user']['token'] . '%' . $this->_config['identifier']));
	}

	/**
	 * Check a token sent with a (likely destructive) request.
	 *
	 * @param string $name          The name of the token
	 * @param string $value         The token value that was sent
	 * @param string $langException A language key to use instead of the default if the token doesn't match
	 */
	public function checkToken($name, $value, $langException = null)
	{
		if ($value !== $this->createToken($name))
		{
			throw new Exception($langException ? $this->lang($langException) : 'exceptions.modules.invalid_csrf');
		}
	}

	/**
	 * Method to run when this module is installed
	 */
	public function install()
	{
	}

	/**
	 * Method to run when this module is uninstalled
	 */
	public function uninstall()
	{
	}
}