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

use smCore\Application, smCore\Autoloader, smCore\Exception, smCore\Module, smCore\Settings, smCore\FileIO\Factory as IOFactory;
use DirectoryIterator;

class Modules
{
	protected $_moduleData;
	protected $_modules = array();

	public function __construct()
	{
		$cache = Application::get('cache');

		// Load the configs
		if (false === $this->_moduleData = $cache->load('core_module_registry_data'))
		{
			$this->_moduleData = array();

			// Load internal modules first, then any user-added modules
			$this->_readModulesFromDirectory(dirname(__DIR__) . '/Modules');
			$this->_readModulesFromDirectory(Settings::MODULE_DIR);

			$cache->save('core_module_registry_data', $this->_moduleData);

			// @todo: cache tags
			// Anything that depends on this should be refreshed
			// $cache->clean('dependency_module_registry');
		}

		foreach ($this->_moduleData as $module)
		{
			new Autoloader($module['config']['namespace'], $module['directory']);
		}
	}

	protected function _readModulesFromDirectory($directory)
	{
		$reader = IOFactory::getReader('yaml');
		$iterator = new DirectoryIterator($directory);

		foreach ($iterator as $module)
		{
			if ($module->isDot() || !$module->isDir() || !file_exists($module->getPathname() . '/config.yml'))
			{
				continue;
			}

			try
			{
				$config = $reader->read($module->getPathname() . '/config.yml');

				if (empty($config))
				{
					continue;
				}
			}
			catch (\Exception $e)
			{
				throw new Exception(sprintf('Error parsing module config file "%s": %s', $module->getPathname() . '/config.yml', $e->getMessage()));
			}

			if (empty($config['identifier']))
			{
				throw new Exception(array('exceptions.modules.no_identifier', basename($module->getPathname())));
			}

			if (array_key_exists($config['identifier'], $this->_moduleData))
			{
				throw new Exception(array(
					'exceptions.modules.identifier_taken',
					basename($module->getPathname()),
					basename($this->_moduleData[$config['identifier']]['directory'])
				));
			}

			$this->_moduleData[$config['identifier']] = array(
				'config' => $config,
				'directory' => $module->getPathname(),
			);
		}
	}

	public function getLoadedModules()
	{
		return $this->_modules;
	}

	public function getModule($identifier)
	{
		if (!array_key_exists($identifier, $this->_modules))
		{
			if (array_key_exists($identifier, $this->_moduleData))
			{
				$moduleClass = $this->_moduleData[$identifier]['config']['namespace'] . '\\Module';

				if (!class_exists($moduleClass))
				{
					$moduleClass = 'smCore\\Module';
				}

				$this->_modules[$identifier] = new $moduleClass($this->_moduleData[$identifier]['config'], $this->_moduleData[$identifier]['directory']);
			}
			else
			{
				throw new Exception('exceptions.modules.doesnt_exist', $identifier);
			}
		}

		return $this->_modules[$identifier];
	}

	public function getModuleConfig($identifier)
	{
		if (!array_key_exists($identifier, $this->_moduleData))
		{
			return false;
		}

		return $this->_moduleData[$identifier]['config'];
	}

	public function getIdentifiers()
	{
		return array_keys($this->_moduleData);
	}

	public function moduleExists($identifier)
	{
		return array_key_exists($identifier, $this->_moduleData);
	}
}