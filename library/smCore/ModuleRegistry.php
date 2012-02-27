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
use smCore\Application, smCore\Exception, smCore\model\Module, smCore\filesystem\FileIOFactory, Settings,
	DirectoryIterator, Cache;

/**
 * Keeps track of all of the Modules, eventually config files and
 *  stuff like that
 */
class ModuleRegistry
{
	private static $_modules = array();
	private static $_moduleData = array();

	private static $_instance = null;

	/**
	 * Make me one of these registry things. Ktnx.
	 */
	protected function __construct()
	{
		// Make sure the modules cache is up to date
		self::recompile();
	}

	/**
	 * @static
	 * @return \smCore\ModuleRegistry
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * Load the list of modules: read the configs and set up an array of modules information.
	 */
	static function recompile()
	{
		// Load the configs
		$iterator = new DirectoryIterator(Settings::APP_MODULE_DIR);
		self::$_modules = array();

		foreach ($iterator as $module)
		{
			if ($module->isDot() || !$module->isDir() || !file_exists($module->getPathname() . '/config.yaml'))
				continue;

			$reader = FileIOFactory::getReader('yaml');
			$config = $reader->read($module->getPathname() . '/config.yaml');

			if (empty($config['identifier']))
				throw new Exception(array('exceptions.modules.no_identifier', basename($module->getPathname())));

			if (array_key_exists($config['identifier'], self::$_modules))
				throw new Exception(array(
										 'exceptions.modules.identifier_taken',
										 basename($module->getPathname()),
										 basename(self::$_modules[$config['identifier']]['directory'])
									));

			self::$_moduleData[$config['identifier']] = array(
				'config' => $config,
				'directory' => $module->getPathname(),
			);
			// @todo hacky
			// self::$_moduleData[$config['identifier']]['namespace'] = self::$_moduleData[$config['identifier']]['template_ns'];
		}
	}

	/**
	 * All loaded Modules.
	 *
	 * @return array
	 */
	public function getLoadedModules()
	{
		return self::$_modules;
	}

	/**
	 * Retrieve a Module.
	 *
	 * @param $identifier
	 * @return Module
	 * @throws Exception
	 */
	public function getModule($identifier)
	{
		if (!array_key_exists($identifier, self::$_modules))
		{
			if (array_key_exists($identifier, self::$_moduleData))
			{
				$moduleClass = self::$_moduleData[$identifier]['config']['namespace'] . '\\' . ucfirst(substr(self::$_moduleData[$identifier]['directory'], strrpos(self::$_moduleData[$identifier]['directory'], '/') + 1)) . 'Module';
				self::$_modules[$identifier] = new $moduleClass($identifier, self::$_moduleData[$identifier]['config'], self::$_moduleData[$identifier]['directory']);
			}
			else
			{
				throw new Exception('exceptions.modules.doesnt_exist', $identifier);
			}
		}

		return self::$_modules[$identifier];
	}

	/**
	 * Retrieve the module configuration as an array...
	 *
	 * @static
	 * @param $identifier
	 * @return bool
	 */
	public static function getModuleConfig($identifier)
	{
		if (!array_key_exists($identifier, self::$_moduleData))
			return false;

		return self::$_moduleData[$identifier]['config'];
	}

	/**
	 * Get all identifiers for the modules we know of.
	 *
	 * @static
	 * @return array
	 */
	public static function getIdentifiers()
	{
		return array_keys(self::$_moduleData);
	}

	/**
	 * Whether the module with the given identifier is known.
	 *
	 * @static
	 * @param $identifier
	 * @return bool
	 */
	public static function moduleExists($identifier)
	{
		return array_key_exists($identifier, self::$_moduleData);
	}
}