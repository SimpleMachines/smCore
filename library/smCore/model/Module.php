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
use smCore\Application, smCore\ModuleRegistry, smCore\events\Event, smCore\views\ViewManager,
	smCore\Language, smCore\Exception, smCore\AccessManager, Settings;

/**
 * Module class represents a module. It handles common operations that are
 * taking place on/with a module, such as initializing it, loading its needed
 * files (language files, templates), and others.
 */
class Module extends Package
{
	public $identifier = '';

	protected $_template_dir = null;
	protected $_language_dir = null;

	protected $_directory;
	protected $_config;

	protected $_controller = null;
	protected $_models = array();

	private $_loaded = false;

	/**
	 * Make a module.
	 *
	 * @param string $identifier
	 * @param string $config
	 * @param string $directory
	 * @throws Exception
	 */
	public function __construct($identifier, $config, $directory)
	{
		if (empty($identifier))
			throw new Exception('module_no_identifier');

		// Might want to use a setter and check the identifier.
		$this->identifier = $identifier;

		// ditto
		$this->_config = $config;
		$this->_directory = $directory;

		$this->_template_dir = $this->_directory . '/templates/';
		$this->_language_dir = $this->_directory . '/languages/';

		if (!empty($this->_config['template_ns']))
			ViewManager::theme()->addNamespace($this->_config['template_ns'], $config['identifier']);

		if (empty($this->_config['settings']))
			$this->_config['settings'] = array();
	}

	/**
	 * Load the controller
	 *
	 * @param string $name
	 *
	 * @access public
	 */
	public function loadController($name)
	{
		if (!file_exists($this->_directory . '/controller/' . $name . '.php'))
			throw new Exception(array('exceptions.modules.invalid_controller', $name));

		$controllerClass = $this->_config['namespace'] . '\\controller\\' . $name;
		$this->_controller = new $controllerClass($this);
	}

	/**
	 * Return the model
	 *
	 * @param string $name
	 * @return object
	 *
	 * @access public
	 */
	public function getModel($name)
	{
		if (!file_exists($this->_directory . '/Models/' . $name . '.php'))
			throw new Exception(array('exceptions.modules.invalid_model', $name));

		$modelClass = $this->_config['namespace'] . '\\Models\\' . $name;
		$this->_models[$name] = new $modelClass($this);

		return $this->_models[$name];
	}

	/**
	 * Run the controller method.
	 *
	 * @param string $name
	 *
	 * @access public
	 */
	public function runControllerMethod($name)
	{
		if ($this->_controller === null)
			throw new Exception('exceptions.modules.no_methods_before_load');

		$method = array($this->_controller, $name);

		if (!is_callable($method))
			throw new Exception('exceptions.modules.method_not_callable');

		$this->_controller->preDispatch();
		call_user_func($method);
		$this->_controller->postDispatch();
	}

	/**
	 * Loading the module. Well it's more of an activation, what we do here.
	 * The module isn't active (not added to permissions, to routes, to events)
	 * before this.
	 *
	 * @throws Exception
	 */
	public function load()
	{
		AccessManager::getInstance()->addResource($this->identifier);
		if (!empty($this->_config['template_ns']))
			ViewManager::theme()->addNamespace($this->_config['template_ns'], $this->identifier);

		// probably more here.

		$this->_loaded = true;
	}

	/**
	 * Return whether this package is loaded.
	 *
	 * @return bool
	 */
	function isLoaded()
	{
		return $this->_loaded;
	}

	/**
	 * Tells whether the current user has a particular module-defined privilege
	 *
	 * @param $privilege
	 * @return bool
	 */
	public function isAllowed($privilege = null)
	{
		return User::getCurrentUser()->isAllowed($this->identifier, $privilege);
	}

	/**
	 * Load templates. Should find them in their directory.
	 * @param string $name
	 */
	function loadTemplates($name)
	{
		ViewManager::theme()->loadTemplates($this->_template_dir . $name . '.tox');
	}

	/**
	 * Add a template...
	 * @param string $name
	 */
	function addTemplate($name)
	{
		ViewManager::theme()->addTemplate($name, $this->_config['template_ns']);
	}

	/**
	 * Load language. Should find it in its directory.
	 * @param string $filename
	 * @param bool $force_reload
	 */
	function loadLanguage($filename, $force_reload = false)
	{
		// If a full filename was passed in, just load it directly
		if (file_exists($filename))
			Language::getLanguage()->load($filename, $force_reload);
		else
			Language::getLanguage()->load($this->_language_dir . $filename, $force_reload);
	}

	/**
	 * Get language string.
	 * For modules convenience, this is a service we provide.
	 * (module controllers will have it accessible)
	 *
	 * @param string|array $key
	 * @param array $replacements
	 * @return array|string
	 */
	public function lang($key, array $replacements = array())
	{
		if (is_array($key))
			array_unshift($key, $this->_config['language_ns']);
		else
			$key = array($this->_config['language_ns'], $key);

		return Language::getLanguage()->get($key, $replacements);
	}

	/**
	 * A shortcut to allow us to throw language strings in exceptions
	 *
	 * @param string|array $key
	 * @param array $replacements
	 * @throws \smCore\Exception
	 *
	 * @access public
	 */
	public function throwLangException($key, array $replacements = array())
	{
		throw new Exception($this->lang($key, $replacements));
	}

	/**
	 * Events creation.
	 *
	 * @param $name
	 * @param array $args
	 * @return \smCore\model\Event
	 */
	public function createEvent($name, array $args = array())
	{
		// Use proper namespacing - "com.fustrate.calendar" + "load" = "com.fustrate.calendar.load"
		return new Event($this, $this->identifier . '.' . $name, $args);
	}

	/**
	 * Create the token.
	 *
	 * @param string $name
	 * @return string
	 *
	 * @access public
	 */
	public function createToken($name)
	{
		return md5(hash('sha256', $name . '%' . User::getCurrentUser()->getToken() . '%' . $this->_config['identifier']));
	}

	/**
	 * Check the token.
	 *
	 * @param string $name
	 * @param string $value
	 * @param Exception $langException=null
	 * @throws Exception
	 *
	 * @access public
	 */
	public function checkToken($name, $value, $langException = null)
	{
		if ($value !== $this->createToken($name))
		{
			throw new Exception($langException ? $this->lang($langException) : 'exceptions.modules.invalid_csrf');
		}
	}

	/**
	 * Settings. Allows to retrieve a setting of this module.
	 *
	 * @param $name
	 * @return string
	 */
	public function getSetting($name)
	{
		if (array_key_exists($name, $this->_config['settings']))
			return $this->_config['settings'][$name];

		return null;
	}

	/**
	 * Return the directory of this Module.
	 *
	 * @return string
	 */
	public function getDirectory()
	{
		return $this->_directory;
	}

	/**
	 * Allows to set the directory in which this module lives.
	 *
	 * @param $directory
	 */
	public function setDirectory($directory)
	{
		// @todo check what you get
		$this->_directory = $directory;
	}

	/**
	 * Return the events this module is registered to listen for.
	 * @return boolean|array:events
	 */
	public function getEvents()
	{
		if (!empty($this->_config['events']))
			return $this->_config['events'];
		return false;
	}

	/**
	 * Construct URL for this module action.
	 *
	 * @param $action
	 * @return string
	 */
	public function getUrl($action = null)
	{
		// Construct URL for this module action.
		if (!empty($this->_config['name']) && !empty($action))
			return Settings::APP_URL . '/' . $this->_config['name'] . '/' . $action;
		elseif (!empty($this->_config['name']))
			return Settings::APP_URL . '/' . $this->_config['name'];

		// We don't know who we are, so...
		return Settings::APP_URL;
	}

	/**
	 * Return whether this module is installed.
	 */
	function isInstalled()
	{
		//
	}

	/**
	 * Install this module...
	 */
	function install()
	{
		//
	}

	/**
	 * Perform the necessary checks that the module could do, to install.
	 * dry-run install.
	 */
	function testInstall()
	{
		//
	}

	/**
	 * Uninstall this module.
	 * @throws Exception
	 */
	function uninstall()
	{
		//
	}

	/**
	 * Enable this module.
	 *
	 * @throws Exception
	 */
	function enable()
	{
		//
	}

	/**
	 * Disable this module.
	 *
	 * @throws Exception
	 */
	function disable()
	{
		//
	}
}
