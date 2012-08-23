<?php

/**
 * smCore Language Model Class
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

namespace smCore\Model;

use smCore\FileIO\Factory as IOFactory;

class Language extends AbstractModel
{
	// Stores all the language strings
	protected $_strings = array();
	protected $_packageData;

	protected $_name;
	protected $_code;
	protected $_id;
 
	/**
	 * Loads the basic language file and sets up stuff
	 * 
	 * @param \smCore\Application $app
	 * @param string              $name The language name, i.e. 'English (American)'
	 * @param string              $code The language code, i.e. 'en_US'
	 * @param int                 $id
	 */
	public function __construct($app, $name, $code, $id = 0)
	{
		parent::__construct($app);

		$this->_name = $name;
		$this->_code = $code;
		$this->_id = (int) $id;
	}

	/**
	 * Search the cache for a package's strings, falling back to the database on failure
	 *
	 * @return array
	 */
	protected function _getPackageData()
	{
		if (null === $this->_packageData)
		{
			$cache = $this->_app['cache'];

			if (false === $this->_packageData = $cache->load('smcore_language_packages'))
			{
				$db = $this->_app['db'];

				$result = $db->query("
					SELECT id_package, package_name, package_type
					FROM {db_prefix}lang_packages"
				);

				$this->_packageData = array(
					'types' => array(),
					'names' => array(),
				);

				if ($result->rowCount() > 0)
				{
					while ($package = $result->fetch())
					{
						$this->_packageData['types'][$package['package_type']][] = (int) $package['id_package'];
						$this->_packageData['names'][$package['package_name']] = (int) $package['id_package'];
					}
				}

				$cache->save('smcore_language_packages', $this->_packageData);
			}
		}

		return $this->_packageData;
	}

	/**
	 * Load all packages of a given type, i.e. menu
	 *
	 * @param string  $type
	 * @param boolean $force_recompile
	 *
	 * @return self
	 */
	public function loadPackagesByType($type, $force_recompile = false)
	{
		$packages = $this->_getPackageData();

		if (empty($packages['types'][$type]))
		{
			return;
		}

		foreach ($packages['types'][$type] as $package)
		{
			$this->_loadPackageById($package, $force_recompile);
		}

		return $this;
	}

	/**
	 * Load a package by name
	 *
	 * @param string $name
	 * @param boolean $force_recompile
	 *
	 * @return self
	 */
	public function loadPackageByName($name, $force_recompile = false)
	{
		$packages = $this->_getPackageData();

		if (empty($packages['names'][$name]))
		{
			return;
		}

		$this->_loadPackageById($packages['names'][$name], $force_recompile);

		return $this;
	}

	/**
	 * Load a package by internal ID
	 *
	 * @param int $id_package
	 * @param boolean $force_recompile
	 */
	protected function _loadPackageById($id_package, $force_recompile)
	{
		$cache = $this->_app['cache'];

		$cache_key = 'lang_package_' . (int) $id_package;

		if ($force_recompile || false === $data = $cache->load($cache_key))
		{
			$db = $this->_app['db'];

			$result = $db->query("
				SELECT string_key, string_value
				FROM {db_prefix}lang_strings
				WHERE string_package = {int:id}",
				array(
					'id' => $id_package,
				)
			);

			$strings = $result->fetchAll();

			$data = array();

			foreach ($strings as $row)
			{
				$data[$row['string_key']] = $row['string_value'];
			}

			$cache->save($cache_key, $data);
		}

		$this->_addStrings($data);
	}

	/**
	 * Merge the new strings onto the existing strings.
	 *
	 * @param array $strings An array of strings
	 */
	protected function _addStrings($strings)
	{
		$this->_strings = array_merge($this->_strings, $strings);
	}

	/**
	 * Generic keyExists function, lets us know if a specific language key is in the currently loaded strings.
	 *
	 * @param string $key The key to search for. If this is an array, it will be imploded by the key separator.
	 *
	 * @return boolean Whether or not the key could be found.
	 */
	public function keyExists($key)
	{
		if (empty($key) || !is_string($key))
		{
			throw new Exception('exceptions.lang.empty_index');
		}

		return array_key_exists($key, $this->_strings);
	}

	/**
	 * Retrieve a language string based on index.
	 *
	 * @param string $key          The language string to look for.
	 * @param array  $replacements Array of replacements to sprintf into the string if found.
	 *
	 * @return string
	 */
	public function get($key, $replacements = array())
	{
		if (empty($key) || !is_string($key))
		{
			throw new Exception('exceptions.lang.empty_index');
		}

		if ($this->keyExists($key))
		{
			if (!empty($replacements))
			{
				return vsprintf($this->_strings[$key], $replacements);
			}

			return $this->_strings[$key];
		}

 		// Return the key, so we at least know what's not there
		return $key;
	}
}