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
	/**
	 * Once registered, this method is called by the PHP engine when loading a class.
	 * It allows us to figure out where the class is, based on our setup of directories and files,
	 * and have the respective file included.
	 *
	 * @param string $name The name of the class we're trying to find.
	 *
	 * @access public
	 */
	public static function autoload($name)
	{
		// Namespaced, like smCore\Router
		if (strpos($name, '\\') !== false)
		{
			if (strpos($name, 'smCore\\Modules\\') === 0)
			{
				// First 15 characters (0-14) are "smCore\Modules\", so cut them off
				$filename = Settings::MODULE_DIR . '/' . str_replace('\\', '/', substr($name, 15)) . '.php';
			}
			else
			{
				$filename = dirname(__DIR__) . '/' . str_replace('\\', '/', $name) . '.php';
			}
		}
		else
		{
			// Otherwise, it's not namespaced and we'll hope it has a standard format
			$filename = dirname(__DIR__) . '/' . str_replace('_', '/', $name) . '.php';
		}

		if (file_exists($filename))
			require $filename;
	}

	/**
	 * Register this autoloader.
	 */
	public static function register()
	{
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	/**
	 * Unregister this autoloader.
	 */
	public static function unregister()
	{
		spl_autoload_unregister(array(__CLASS__, 'autoload'));

		$functions = spl_autoload_functions();

		if (empty($functions))
			spl_autoload_register(array(__CLASS__, 'defaultAutoload'));
	}

	/**
	 * If our autoloader gets unregistered, the default doesn't get restored. This is a
	 * basic pass-through to the default. We don't just set it to __autoload here because
	 * it might not be defined yet.
	 *
	 * @param string $name Name of the class to autoload.
	 *
	 * @access public
	 */
	public static function defaultAutoload($name)
	{
		if (function_exists('__autoload'))
			__autoload($name);
	}
}