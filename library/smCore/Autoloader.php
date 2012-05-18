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
	 */
	public static function autoload($name)
	{
		// Modules aren't located here, so we'll work some magic.
		if (strpos($name, 'smCore\\Modules\\') === 0)
		{
			// First 15 characters (0-14) are "smCore\Modules\", so cut them off
			$filename = Settings::MODULE_DIR . '/' . str_replace(array('\\', '_', "\0"), array('/', '/', ''), substr($name, 15)) . '.php';
		}
		else
		{
			// Otherwise, it's not namespaced and we'll hope it has a standard format
			$filename = dirname(__DIR__) . '/' . str_replace(array('\\', '_', "\0"), array('/', '/', ''), $name) . '.php';
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
}