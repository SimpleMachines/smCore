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

namespace smCore\filesystem;
use smCore\Exception;

/**
 * Factory for file I/O operations. Creates the appropriate type of FileReader or FileWriter.
 * FileReaders/Writers 'know' how to handle a particular format of input (or output), i.e. they're specific to
 * Json input file or output format, or XML, or others.
 */
class FileIOFactory
{
	private static $_readers;
	private static $_writers;
	private static $_default;

	/**
	 * Private constructor. Instances of this class cannot be created.
	 */
	private function __construct()
	{
		// I think we don't need one o'these.
	}

	/**
	 * Initialize if necessary and return a FileReader of the specified format/type.
	 *
	 * @param string $type
	 * @throws Exception
	 */
	public static function getReader($type = null)
	{
		if (empty(self::$_readers))
			self::$_readers = array();
		if (empty($type))
			$type = self::getDefault();
		try
		{
			switch ($type)
			{
				case 'yaml':
					if (empty(self::$_readers['yaml']))
						self::$_readers['yaml'] = new YamlFileReader();
					return self::$_readers['yaml'];
				break;

				// @todo insert other readers
				// (or not, and change this to plug in readers.)

				default:
					throw new \Exception(); // or else throw directly here the 'file_reader_not_loaded'
				break;
			}
		}
		catch (\Exception $exception)
		{
			// @todo add to language strings.
			// Something like "Make sure you have all libraries installed for the
			// reader you want to use.
			throw new Exception('file_reader_not_loaded');
		}
	}

	/**
	 * Initialize if necessary and return a FileWriter of the specified format/type.
	 * @param string $type
	 * @throws Exception
	 */
	public static function getWriter($type = null)
	{
		if (empty(self::$_writers))
			self::$_writers = array();
		if (empty($type))
			$type = self::getDefault();
		try
		{
			switch ($type)
			{
				case 'yaml':
					if (empty(self::$_writers['yaml']))
						self::$_writers['yaml'] = new YamlFileReader();
					return self::$_writers['yaml'];
				break;

				// @todo insert other writers
				// (or not, and change this to plug in writers.)

				default:
					throw new \Exception(); // or else throw directly here the 'file_writer_not_loaded'
				break;
			}
		}
		catch (Exception $exception)
		{
			// @todo add to language strings.
			// Something like "Make sure you have all libraries installed for the
			// writer you want to use.
			throw new Exception('file_writer_not_loaded');
		}
	}

	/**
	 * Return the default format this factory will create, if no type is specified.
	 *
	 * @static
	 * @return string
	 */
	public static function getDefault()
	{
		// have a sane default...
		if (empty(self::$_default))
			self::$_default = 'yaml';
		return self::$_default;
	}

	/**
	 * Set the default format.
	 *
	 * @static
	 * @param $default
	 */
	public static function setDefault($default)
	{
		// could have a list of installed readers/writers and accept only those.
		self::$_default = $default;
	}
}
