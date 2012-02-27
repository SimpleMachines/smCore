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
 *
 */

namespace smCore;
use Settings;

/**
 * Default autoloader.
 * Registers paths from smCore and modules, to load the files.
 */
class DefaultAutoloader
{
	/**
	 * Once registered, this method is called by the PHP engine when loading a class.
	 * It allows us to figure out where the class is, based on our setup of directories and files,
	 * and have the respective file included.
	 *
	 * @static
	 * @param $name
	 */
	public static function autoload($name)
	{
		$name = trim($name, '\\');
		$filename = null;

		// Namespaced, like smCore\Router
		if (strpos($name, 'modules\\') === 0 || (strpos($name, 'smCore\\modules\\') === 0))
		{
			// If first 13 characters (0-15) are "smCore\modules\", cut them off
			if (strpos($name, 'smCore\\modules\\') === 0)
				$filename = Settings::APP_MODULE_DIR . '/' . str_replace('\\', '/', substr($name, 15)) . '.php';
			else
				$filename = Settings::APP_MODULE_DIR . '/' . str_replace('\\', '/', substr($name, 8)) . '.php';
		}
		elseif (strpos($name, 'smCore\\') === 0)
		{
			// if it's a storage class, then load it from its layer
			str_replace('\\storage\\', '\\' . Settings::$database['layer'] . 'storage\\', $name);
			// fully namespaced
            if (strpos($name, 'smCore\\TemplateEngine\\') === 0)
                $filename = Settings::APP_PATH . '/library/smTE/include/' . str_replace('\\', '/', substr($name, 22)) . '.php';
            else
			    $filename = Settings::APP_PATH . '/library/' . str_replace('\\', '/', $name) . '.php';
		}
		elseif (strpos($name, 'database\\') === 0)
		{
			$filename = Settings::APP_PATH . '/library/' . str_replace('\\', '/', $name) . '.php';
		}
		elseif (strpos($name, '\\') > 0)
		{
			$filename = __DIR__ . '/' . str_replace('\\', '/', $name) . '.php';
		}
		// Not namespaced
		else
		{
			if (substr($name, 0, 6) === 'sfYaml')
				$filename = Settings::APP_PATH . '/library/sfYaml/' . $name . '.php';
			elseif (file_exists(Settings::APP_PATH . '/library/' . $name . '.php'))
				$filename = Settings::APP_PATH . '/library/' . $name . '.php';
			elseif (file_exists(Settings::APP_PATH . '/library/Inspekt/' . $name . '.php'))
				$filename = Settings::APP_PATH . '/library/Inspekt/' . $name . '.php';
			else
				$filename = Settings::APP_PATH . '/library/' . str_replace('_', '/', $name) . '.php';
		}

		if (file_exists($filename))
			include_once($filename);
		else
		{
			// @todo remove debugging help code! (yes, I'm lazy)
			echo $filename;
			echo ' ' . $name;
		}
	}

	/**
	 * Register this autoloader.
	 *
	 * @static
	 * @return bool
	 */
	public static function register()
    {
		return spl_autoload_register(array(__CLASS__, 'autoload'));
    }

	/**
	 * Unregister our autoloader.
	 *
	 * @static
	 * @return bool
	 */
	public static function unregister()
	{
		return spl_autoload_unregister(array(__CLASS__, 'autoload'));
	}
}