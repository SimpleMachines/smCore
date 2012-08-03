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

use smCore\Application, smCore\FileIO\Factory as IOFactory;

class Language
{
	// Stores all the language strings
	protected $_strings = array();
	protected $_packageData;

	protected $_name;
	protected $_code;
	protected $_id;
 
	// Loads the basic language file and sets up stuff
	public function __construct($name, $code, $id = 0)
	{
		$this->_name = $name;
		$this->_code = $code;
		$this->_id = (int) $id;
	}

	protected function _getPackageData()
	{
		if (null === $this->_packageData)
		{
			$cache = Application::get('cache');

//			if (false === $this->_packageData = $cache->load('smcore_language_packages'))
			{
				$db = Application::get('db');

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

	protected function _loadPackageById($id_package, $force_recompile)
	{
		$cache = Application::get('cache');

		$cache_key = 'lang_package_' . (int) $id_package;

		if ($force_recompile || false === $data = $cache->load($cache_key))
		{
			$db = Application::get('db');

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

			$cache->save($cache_key, $data, array('smcore_language', 'smcore_language_' . $this->_code));
		}

		$this->_addStrings($data);
	}

	/**
	 * Merge the new strings onto the existing strings.
	 *
	 * @param array $strings An array of strings, with their keys flattened via _compileStrings()
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


	/**
	 * Loads and compiles a language file
	 *
	 * @param string  $filename        File to load
	 * @param boolean $force_recompile Should we ignore the cache and recompile anyways?
	 *
	 * @todo better language filenames
	 */
	public function load($base_filename, $force_recompile = false)
	{
		$cache = Application::get('cache');
		$path = pathinfo($base_filename);
		$files = array();

		$cache_name = 'lang_' . $this->language . '_' . preg_replace('~[^a-z0-9_]~i', '_', $base_filename);
		$cache_metadata = $cache->getMetadatas($cache_name);

		// Force a forced recompile if the file or its English (US) counterpart has been modified since it was cached
		// There may not be a version of this language file in the visitor's language

		if (file_exists($base_filename))
		{
			$force_recompile |= filemtime($base_filename) > $cache_metadata['mtime'];

			$files[] = $base_filename;
		}

		if ($this->language !== 'english_us')
		{
			$lang_filename = $path['dirname'] . '/' . $this->language . '/' . $path['basename'];

			if (file_exists($lang_filename))
			{
				$force_recompile |= filemtime($lang_filename) > $cache_metadata['mtime'];

				$files[] = $lang_filename;
			}
		}

		if ($force_recompile || $false === $compiled = $cache->load($cache_name))
		{
			$strings = array();

			if (empty($files))
				throw new Exception(sprintf('Could not find a language file with the filename "%s".', $base_filename));

			foreach ($files as $file)
				$strings = array_merge($strings, $this->_getFromFile($file, $path['extension']));

			$compiled = array();
			$this->_compileStrings(array(), $strings, $compiled);

			$cache->save($cache_name, $compiled, array('core_language', 'core_language_' . $this->language));
		}

		$this->_addStrings($compiled);
	}

	/**
	 * Load the file and extract the strings
	 *
	 * @param string $filename
	 * @param string $ext Extension of the file, passed this way since we'll already have it when this is called. Why not use it?
	 * @return array Strings which were loaded from the file
	 *
	 * @access protected
	 */
	protected function _getFromFile($filename, $ext)
	{
		// What is this file's extension?
		$reader = IOFactory::getReader($ext);

		if ($reader === null)
			throw new Exception('exceptions.lang.invalid_format');

		$strings = $reader::read($filename);

		if ($strings === null)
		{
			// @todo: do something
		}

		return $strings;
	}

	/**
	 * Compile an array of language strings into a string-imploded array
	 *
	 * This helps with retrieval for humans, who would rather not type out arrays, and machines
	 * which may or may not like to be speedier in their lookups.
	 *
	 * @param array $base     The base of the key for this level, to be imploded by $separator
	 * @param array $strings  A key=>value array of strings to compile
	 * @param array $compiled The array in which to store compiled values, passed by reference
	 */
	protected function _compileStrings($base, $strings, &$compiled)
	{
		foreach ($strings as $key => $val)
		{
			$current_key = array_merge($base, array($key));

			if (is_array($val))
			{
				$this->_compileStrings($current_key, $val, $compiled);
			}
			else
			{
				$compiled[implode('.', $current_key)] = $val;
			}
		}
	}
}