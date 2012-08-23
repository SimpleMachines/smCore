<?php

/**
 * smCore FileIO Factory
 *
 * Factory for file I/O operations. Creates the appropriate type of FileReader or FileWriter.
 * FileReaders/Writers 'know' how to handle a particular format of input (or output), i.e. they're specific to
 * Json input file or output format, or XML, or others.
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

namespace smCore\FileIO;

use smCore\Exception;

class Factory
{
	protected static $_readers = array();
	protected static $_writers = array();
	protected static $_default;

	/**
	 * Private constructor. Instances of this class cannot be created.
	 *
	 * @access private
	 */
	private function __construct()
	{
	}

	public static function typeForFile($filename)
	{
		$ext = end(explode('.', $filename));

		$types = array(
			'csv' => 'csv',
			'ini' => 'ini',
			'json' => 'json',
			'xml' => 'xml',
			'yaml' => 'yaml',
			'yml' => 'yaml',
		);

		// @todo: extensibility

		if (!empty($types[$ext]))
		{
			return $types[$ext];
		}

		return null;
	}

	/**
	 * Initialize if necessary and return a FileReader of the specified format/type.
	 *
	 * @param string $type
	 *
	 * @return smCore\FileIO\Reader|null
	 */
	public static function getReader($type)
	{
		$type = strtolower($type);

		if ('yaml' === $type)
		{
			if (empty(self::$_readers['yaml']))
			{
				self::$_readers['yaml'] = new YamlReader();
			}

			return self::$_readers['yaml'];
		}

		if ('xml' === $type)
		{
			if (empty(self::$_readers['xml']))
			{
				self::$_readers['xml'] = new XmlReader();
			}

			return self::$_readers['xml'];
		}

		if ('json' === $type)
		{
			if (empty(self::$_readers['json']))
			{
				self::$_readers['json'] = new JsonReader();
			}

			return self::$_readers['json'];
		}

		if ('ini' === $type)
		{
			if (empty(self::$_readers['ini']))
			{
				self::$_readers['ini'] = new IniReader();
			}

			return self::$_readers['ini'];
		}

		if ('csv' === $type)
		{
			if (empty(self::$_readers['csv']))
			{
				self::$_readers['csv'] = new CSVReader();
			}

			return self::$_readers['csv'];
		}

		// @todo insert other readers
		// (or not, and change this to plug in readers.)

		return null;
	}

	/**
	 * Initialize if necessary and return a FileWriter of the specified format/type.
	 *
	 * @param string $type
	 *
	 * @return smCore\FileIO\Writer|null
	 */
	public static function getWriter($type)
	{
		$type = strtolower($type);

		if ('yaml' === $type)
		{
			if (empty(self::$_writers['yaml']))
			{
				self::$_writers['yaml'] = new YamlWriter();
			}

			return self::$_writers['yaml'];
		}

		if ('xml' === $type)
		{
			if (empty(self::$_writers['xml']))
			{
				self::$_writers['xml'] = new XmlWriter();
			}

			return self::$_writers['xml'];
		}

		if ('json' === $type)
		{
			if (empty(self::$_writers['json']))
			{
				self::$_writers['json'] = new JsonWriter();
			}

			return self::$_writers['json'];
		}

		if ('ini' === $type)
		{
			if (empty(self::$_writers['ini']))
			{
				self::$_writers['ini'] = new IniWriter();
			}

			return self::$_writers['ini'];
		}

		if ('csv' === $type)
		{
			if (empty(self::$_writers['csv']))
			{
				self::$_writers['csv'] = new CSVWriter();
			}

			return self::$_writers['csv'];
		}

		// @todo insert other readers
		// (or not, and change this to plug in readers.)

		return null;
	}

}