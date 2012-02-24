<?php

/**
 * smCore Modules Storage
 *
 * Keeps track of all of the config files and stuff like that.
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

namespace smCore\Storage;
use smCore\Application, smCore\Exception, smCore\Settings, smCore\FileIO\Factory as IOFactory,
	DirectoryIterator, Zend_Cache;

class Modules
{
	protected static $_modules = array();
	protected static $_moduleData = array();

	public function __construct()
	{
		$cache = Application::get('cache');

		// Load the configs
		// if ((self::$_modules = $cache->load('core_module_registry_data')) === false)
		{
			$iterator = new DirectoryIterator(Settings::MODULE_DIR);
			self::$_modules = array();

			$reader = IOFactory::getReader('yaml');

			foreach ($iterator as $module)
			{
				if ($module->isDot() || !$module->isDir() || !file_exists($module->getPathname() . '/config.yaml'))
					continue;

				try
				{
					$config = $reader::read($module->getPathname() . '/config.yaml');

					if (empty($config))
						continue;
				}
				catch (\Exception $e)
				{
					// @todo: error
					continue;
				}

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
			}

			$cache->save(self::$_modules, 'core_module_registry_data');

			// Anything that depends on this should be refreshed
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('dependency_module_registry'));
		}
	}

	public function getLoadedModules()
	{
		return self::$_modules;
	}

	public function getModule($identifier)
	{
		if (!array_key_exists($identifier, self::$_modules))
		{
			if (array_key_exists($identifier, self::$_moduleData))
			{
				$moduleClass = self::$_moduleData[$identifier]['config']['namespace'] . '\\Module';
				self::$_modules[$identifier] = new $moduleClass(self::$_moduleData[$identifier]['config'], self::$_moduleData[$identifier]['directory']);
			}
			else
				throw new Exception('exceptions.modules.doesnt_exist', $identifier);
		}

		return self::$_modules[$identifier];
	}

	public function getModuleConfig($identifier)
	{
		if (!array_key_exists($identifier, self::$_moduleData))
			return false;

		return self::$_moduleData[$identifier]['config'];
	}

	public function getIdentifiers()
	{
		return array_keys(self::$_moduleData);
	}

	public function moduleExists($identifier)
	{
		return array_key_exists($identifier, self::$_moduleData);
	}
}