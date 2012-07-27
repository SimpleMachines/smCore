<?php

/**
 * smCore Theme Class
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

class Theme
{
	function init()
	{
		$user = Application::get('user');
		$cache = Application::get('cache');
		$id = !empty($user['theme']) ? $user['theme'] : 1;

		if (false === $theme = $cache->load('theme_' . $id))
		{
			$db = Application::get('db');

			$result = $db->query("
				SELECT *
				FROM {db_prefix}themes
				WHERE id_theme = {int:id}",
				array(
					'id' => $id,
				)
			);

			// If the user's theme doesn't exist, try the default theme instead
			if ($result->rowCount() < 1 && $id != 1)
			{
				$result = $db->query("
					SELECT *
					FROM {db_prefix}themes
					WHERE id_theme = 1");
			}

			if ($result->rowCount() < 1)
			{
				throw new Exception('exceptions.themes.no_default');
			}
		
			$theme = $result->fetch();
			$cache->save($theme, 'theme_' . $id);
		}
	}

	protected static function _setupTheme()
	{
	}
}