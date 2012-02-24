<?php

/**
 * smCore Storage - Themes
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

namespace smCore\Storages;

class Themes
{
	protected static $_themes = array();

	public static function getThemes()
	{
	}

	public static function getThemeById($id)
	{
		if (array_key_exists($id, $_themes))
			return $_themes[$id];

		$result = Application::get('db')->query("
			SELECT *
			FROM beta_themes
			WHERE id_theme = ?",
			array($id)
		);
		
		if ($result->rowCount() < 1)
			return false;
		else
		{
			// Mini-caching
			$_themes[$id] = $result->fetch();
			return $_themes[$id];
		}
	}

	public static function getDefaultTheme()
	{
		return self::getThemeById(Application::APP_DEFAULT_THEME);
	}

	public static function getThemePath($id)
	{
	}
}