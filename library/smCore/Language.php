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
use Settings, smCore\filesystem\FileIOFactory;

/**
 * Language subsystem entry.
 * This class is taking care of the most important internationalization-related tasks.
 */
class Language
{
	/**
	 * Stores all the language strings
	 * @var array
	 */
	protected $language_strings = array();
	public $languageName = 'english_us';

	private static $_defaultLanguage = null;
	private static $_userLanguage = null;

	/**
	 * Loads the basic language file and sets up stuff
	 * @param $name
	 */
	public function __construct($name)
	{
		// Why would there be numbers in a language name, anyways? Maybe for a secondary French language?
		$this->languageName = preg_replace('~[^a-z0-9_-]~', '', strtolower($name));

 		// Load some default strings… disabled for now.
		// $this->load(dirname(__FILE__) . '/languages/' . $this->language . '/common.json');
		// $this->load(dirname(__FILE__) . '/languages/' . $this->language . '/errors.json');
	}

    /**
     * Gets the language string based on index
     * @param $key
     * @param array $replacements
     * @return array|string
     */
	public function get($key, $replacements = array())
	{
		if (empty($key))
			throw new Exception('core_lang_empty_index_sent');

		if (is_array($key))
		{
			$preserve_key = $key;
			$search = $this->language_strings;

			// Drill down, array by array, until we reach our destination.
			while (($part = array_shift($key)) !== null && isset($search[$part]))
				$search = $search[$part];

			if (is_string($search))
			{
				if (!empty($replacements))
					return vsprintf($search, $replacements);

				return $search;
			}

			// Return the key, so we at least know what's not there
			return implode('+', $preserve_key);
		}

		if ($this->keyExists($key))
		{
			if (!empty($replacements))
				return vsprintf($this->language_strings[$key], $replacements);

			return $this->language_strings[$key];
		}

 		// Return the key, so we at least know what's not there
		return $key;
	}

	/**
	 * Loads the basic language file and sets up stuff
	 * @param string $filename
	 * @param bool $force_reload
	 */
	public function load($filename, $force_reload = false)
	{
		$path = pathinfo($filename);

		// Automagic the language name into the filename so that the modules don't have to worry about it.
		$lang_file = $path['dirname'] . '/' . $this->languageName . '.' . $path['basename'];
		$english_lang_file = $path['dirname'] . '/english_us.' . $path['basename'];

		// load/reload
        $strings = array();
		$loaded = false;

		// Let's actually try loading this file twice - first in english, then overwrite with the user's language
		if ($this->languageName !== 'english_us' && file_exists($english_lang_file))
		{
			$strings = $this->_getFromFile($english_lang_file, $path['extension']);
			$loaded = true;
		}

		if (file_exists($lang_file))
		{
			$strings = array_merge($strings, $this->_getFromFile($lang_file, $path['extension']));
			$loaded = true;
		}

		if (!$loaded)
			throw new Exception(array('core_lang_file_not_found', $lang_file));

        // cache

		$this->_addStrings($strings);
	}

    /**
     * Return if a corresponding value for this key exists, as you may have guessed.
     * @param $key
     * @return bool
     */
	public function keyExists($key)
	{
		return array_key_exists($key, $this->language_strings);
	}

	/**
	 * Load strings from a file.
	 * @param string $file the file to load from
	 * @param string $ext the file's extension
     * @return
     * @todo extension is redundant, to remove or have a type specified (optionally)
	 */
	protected function _getFromFile($file, $ext)
	{
		// What is this file's extension?
		$ext = strtolower($ext);

		switch (strtolower($ext))
		{
			case 'ini':
				break;

			case 'xml':
				break;

			case 'json':
				break;

			case 'yaml':
			case 'yml':
            case 'yml':
                $reader = FileIOFactory::getReader('yaml');
                $strings = $reader->read($file);
                break;
		}

		return $strings; // $strings->toArray();
	}

	/**
	 * Add the specified strings to those we already know.
	 * @param $strings
	 * @throws smCore\modules\auth\controllers
	 */
	protected function _addStrings($strings)
	{
		$this->language_strings = array_merge($this->language_strings, $strings);
	}

	static function getDefaultLanguage()
	{
		if (empty(self::$_defaultLanguage))
		{
			self::$_defaultLanguage = new Language(Settings::APP_DEFAULT_LANG);
			self::$_defaultLanguage->load(Settings::APP_LANGUAGE_DIR . '/core.yaml');
		}
		return self::$_defaultLanguage;
	}

	static function getUserLanguage($languageName)
	{
		// @todo this would behave unexpectedly when one replaces language.
		if (empty(self::$_userLanguage))
		{
			self::$_userLanguage = new Language($languageName);
			// @todo this will mean we load the core language file twice,
			// anytime the user has another language defined.
			// which is fine as fallback only, which is not happening here.
			self::$_defaultLanguage->load(Settings::APP_LANGUAGE_DIR . '/core.yaml');
		}
		return self::$_userLanguage;
	}

	/**
	 * @static
	 * @return \smCore\Language
	 */
	static function getLanguage()
	{
		if (!empty(self::$_userLanguage))
			return self::$_userLanguage;
		else
			return self::getDefaultLanguage();
	}
}