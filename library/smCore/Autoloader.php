<?php

/**
 * smCore Autoloader Class
 *
 * Registers paths from smCore and modules, to load the files.
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

class Autoloader
{
	protected $namespace;
	protected $directory;

	/**
	 * Create a new autoloader
	 *
	 * @param string $namespace The namespace for which to attempt to autoload files
	 * @param string $directory The directory to look for namespaced files in
	 */
	public function __construct($namespace, $directory)
	{
		$this->namespace = $namespace ? trim($namespace, '\\') : null;
		$this->directory = $directory;

		spl_autoload_register(array($this, 'autoload'));
	}

	/**
	 * Once registered, this method is called by the PHP engine when loading a class.
	 * It allows us to figure out where the class is, based on our setup of directories and files,
	 * and have the respective file included.
	 *
	 * @param string $name The name of the class we're trying to find.
	 */
	public function autoload($name)
	{
		$name = trim($name, '\\');

		// If this autoloader isn't a default and it doesn't match, skip it
		if (null !== $this->namespace && 0 !== strpos($name, $this->namespace))
		{
			return;
		}

		if ($this->namespace === null)
		{
			$filename = $this->directory . '/' . str_replace(array('\\', '_', "\0"), array('/', '/', ''), $name) . '.php';
		}
		else
		{
			$filename = $this->directory . '/' . str_replace(array('\\', '_', "\0"), array('/', '/', ''), substr($name, strlen($this->namespace))) . '.php';
		}

		if (file_exists($filename))
		{
			require $filename;
		}
	}
}