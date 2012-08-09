<?php

/**
 * smCore 
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

namespace smCore\Storage;

use smCore\Application, smCore\Exception;

class Factory
{
	protected static $_storages = array();

	public static function factory($name)
	{
		if (Application::get('sending_output', false) === true)
		{
			throw new Exception('Cannot load storages after output has been started.');
		}

		$name = ucfirst($name);

		if (!empty(self::$_storages[$name]))
		{
			return self::$_storages[$name];
		}

		if (file_exists(__DIR__ . '/' . $name . '.php'))
		{
			$class = 'smCore\\Storage\\' . $name;
			return self::$_storages[$name] = new $class();
		}

		// @todo: throw exception?
		return null;
	}
}