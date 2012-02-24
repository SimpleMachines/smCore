<?php

/**
 * smCore Language Class
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
use smCore\FileIO\Factory as IOFactory;

class Language
{
	// Stores all the language strings
	protected $_strings = array();
	protected $_key_separator = '.';

	public $language = 'english_us';
 
	// Loads the basic language file and sets up stuff
	public function __construct($name)
	{
		$this->language = preg_replace('~[^a-z_-]~i', '', strtolower($name));
	}

	/**
	 * Retrieve a language string based on index. Arrays are imploded by
	 * $this->_key_separator and fed to fierce kittens.
	 *
	 * @param string|array $key The language string to look for.
	 * @param array $replacements Array of replacements to sprintf into the string if found.
	 * @return string
	 *
	 * @access public
	 */
	public function get($key, $replacements = array())
	{
		if (empty($key))
			throw new Exception('exceptions.lang.empty_index');

		if (is_array($key))
			$key = implode($this->_key_separator, $key);

		if ($this->keyExists($key))
		{
			if (!empty($replacements))
				return vsprintf($this->_strings[$key], $replacements);

			return $this->_strings[$key];
		}
 
 		// Return the key, so we at least know what's not there
		return $key;
	}

	/**
	 * Loads and compiles a language file
	 *
	 * @param string $filename File to load
	 * @param boolean $force_recompile Should we ignore the cache and recompile anyways?
	 *
	 * @access public
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

		if ($force_recompile || (($compiled = $cache->load($cache_name)) === false))
		{
			$strings = array();

			if (empty($files))
				throw new Exception(sprintf('Could not find a language file with the filename "%s".', $base_filename));

			foreach ($files as $file)
				$strings = array_merge($strings, $this->_getFromFile($file, $path['extension']));

			$compiled = array();
			$this->_compileStrings(array(), $strings, $compiled);

			$cache->save($compiled, $cache_name, array('core_language', 'core_language_' . $this->language));
		}

		$this->_addStrings($compiled);
	}

	/**
	 * Generic keyExists function, lets us know if a specific language key is in the currently loaded strings.
	 *
	 * @param string|array $key The key to search for. If this is an array, it will be imploded by the key separator.
	 * @return boolean Whether or not the key could be found.
	 *
	 * @access public
	 */
	public function keyExists($key)
	{
		if (is_array($key))
			$key = implode($this->_key_separator, $key);

		return array_key_exists($key, $this->_strings);
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
	 * @param array $base The base of the key for this level, to be imploded by $separator
	 * @param array $strings A key=>value array of strings to compile
	 * @param array $compiled The array in which to store compiled values, passed by reference
	 * @param string $separator String to implode the base+key array with
	 *
	 * @access protected
	 */
	protected function _compileStrings($base, $strings, &$compiled)
	{
		foreach ($strings as $key => $val)
		{
			$current_key = array_merge($base, array($key));

			if (is_array($val))
				$this->_compileStrings($current_key, $val, $compiled);
			else
				$compiled[implode($this->_key_separator, $current_key)] = $val;
		}
	}

	/**
	 * Merge the new strings onto the existing strings.
	 *
	 * @param array $strings An array of strings, with their keys flattened via _compileStrings()
	 *
	 * @access protected
	 */
	protected function _addStrings($strings)
	{
		$this->_strings = array_merge($this->_strings, $strings);
	}
}