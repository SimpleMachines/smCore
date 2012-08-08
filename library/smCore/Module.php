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

class Module
{
	protected $_application;

	protected $_template_dir;
	protected $_lang_prefix = '';

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
	public function __construct($config, $directory)
	{
		$this->_config = $config;
		$this->_directory = $directory;

		$this->_template_dir = $this->_directory . '/Views/';

		if (empty($this->_config['settings']))
		{
			$this->_config['settings'] = array();
		}

		$this->_config['cache_namespace'] = str_replace('.', '_', $this->_config['identifier']);

		if (!empty($this->_config['lang_namespace']))
		{
			$this->_lang_prefix = $this->_config['lang_namespace'] . '.';
		}

		if (is_dir($this->_template_dir))
		{
			Application::get('twig')->getLoader()->addPath($this->_template_dir);
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

		$controllerClass = $this->_config['namespace'] . '\\Controllers\\' . $controller;
		$controllerObject = new $controllerClass($this);

		if (!is_callable(array($controllerObject, $method)))
		{
			throw new Exception(array('exceptions.modules.method_not_callable', $controller, $method));
		}

		$controllerObject->preDispatch();
		$output = $controllerObject->$method();
		$controllerObject->postDispatch();

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

		$modelClass = $this->_config['namespace'] . '\\Models\\' . $name;

		return new $modelClass($this);
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

			$storageClass = $this->_config['namespace'] . '\\Storages\\' . $name;
			$this->_storages[$name] = new $storageClass($this);
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
			Application::set('sending_output', true);
		}

		return Application::get('twig')->render($name . '.html', $context);
	}

	public function display($name, array $context = array(), $sending_output = true)
	{
		if ($sending_output)
		{
			Application::set('sending_output', true);
		}

		Application::get('twig')->display($name . '.html', $context);

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
			Application::get('lang')->loadPackageByName($this->_config['identifier']);
		}
		else
		{
			Application::get('lang')->loadPackageByName($this->_config['identifier'] . '.' . $package_name);
		}
	}

	/**
	 * Lang!
	 *
	 * @param string|array $key
	 * @param array        $replacements
	 *
	 * @return string
	 */
	public function lang($key, array $replacements = array())
	{
		if (Application::get('lang')->keyExists($this->_lang_prefix . $key))
		{
			return Application::get('lang')->get($this->_lang_prefix . $key, $replacements);
		}

		return $key;
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
		throw new Exception($this->lang($this->_lang_prefix . $key, $replacements));
	}

	/**
	 * Create an event, under this module's namespace, that can be fired/used later.
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return \smCore\Event
	 */
	public function createEvent($name, array $args = array())
	{
		// Use proper namespacing - "com.fustrate.calendar" + "load" = "com.fustrate.calendar.load"
		return new Event($this, $this->_config['identifier'] . '.' . $name, $args);
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
		return Application::get('user')->hasPermission($this->_config['identifier'] . '.' . $name);
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

		if (!Application::get('user')->hasPermission($name))
		{
			throw new Exception('You do not have the permissions required to access this page.');
		}

		return $this;
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
		if (array_key_exists($name, $this->_config['settings']))
		{
			return $this->_config['settings'][$name];
		}

		return null;
	}

	/**
	 *
	 *
	 * @param mixed  $data     The value to save to the cache.
	 * @param string $key      Unique key to save this data under, in the module's namespace.
	 * @param array  $tags     Tags for this data, optional.
	 * @param int    $lifetime Amount of time to keep this data in the cache, optional.
	 */
	public function cacheSave($data, $key, array $tags = array(), $lifetime = false)
	{
		if (empty($key))
		{
			throw new Exception('exceptions.modules.invalid_cache_key');
		}

		$tags = array_merge(array($this->_config['identifier']), $tags);

		Application::get('cache')->save($this->_config['cache_namespace'] . '_' . $key, $data, $tags, $lifetime);
	}

	/**
	 *
	 *
	 * @param string $key The key to find in the cache.
	 */
	public function cacheLoad($key)
	{
		if (empty($key))
		{
			throw new Exception('exceptions.modules.invalid_cache_key');
		}

		return Application::get('cache')->load($this->_config['cache_namespace'] . '_' . $key);
	}

	/**
	 *
	 *
	 * @param string $key The key to test for in the cache.
	 */
	public function cacheTest($key)
	{
		if (empty($key))
		{
			throw new Exception('exceptions.modules.invalid_cache_key');
		}

		return Application::get('cache')->test($this->_config['cache_namespace'] . '_' . $key);
	}

	/**
	 * Create a unique token to use for a (likely destructive) request.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function createToken($name)
	{
		$user = Application::get('user');
		return md5(hash('sha256', $name . '%' . $user['user_token'] . '%' . $this->_config['identifier']));
	}

	/**
	 * Check a token sent with a (likely destructive) request.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @throws \smCore\Exception
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