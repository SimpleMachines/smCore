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

class Factory
{
	protected static $_storages = array();

	public static function getStorage($name)
	{
		$name = strtolower($name);

		if ($name === 'themes')
		{
			if (empty(self::$_storages['themes']))
				self::$_storages['themes'] = new Themes();

			return self::$_storages['themes'];
		}

		if ($name === 'roles')
		{
			if (empty(self::$_storages['roles']))
				self::$_storages['roles'] = new Roles();

			return self::$_storages['roles'];
		}

		if ($name === 'users')
		{
			if (empty(self::$_storages['users']))
				self::$_storages['users'] = new Users();

			return self::$_storages['users'];
		}

		if ($name === 'modules')
		{
			if (empty(self::$_storages['modules']))
				self::$_storages['modules'] = new Modules();

			return self::$_storages['modules'];
		}

		if ($name === 'sessions')
		{
			if (empty(self::$_storages['sessions']))
				self::$_storages['sessions'] = new Sessions();

			return self::$_storages['sessions'];
		}

		return null;
	}
}