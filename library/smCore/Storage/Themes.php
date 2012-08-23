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

namespace smCore\Storage;

use smCore\Exception, smCore\Model\Theme;

class Themes extends AbstractStorage
{
	protected $_cache = array();

	public function getAll()
	{
	}

	public function getById($id)
	{
		$cache = $this->_app['cache'];

		if (false === $theme = $cache->load('smcore_theme_' . $id))
		{
			$db = $this->_app['db'];

			$result = $db->query("
				SELECT *
				FROM {db_prefix}themes
				WHERE id_theme = {int:id}",
				array(
					'id' => $id,
				)
			);

			if ($result->rowCount() < 1)
			{
				throw new Exception('exceptions.themes.id_doesnt_exist');
			}

			$theme = $result->fetch();
			$cache->save('smcore_theme_' . $id, $theme);
		}

		return new Theme($theme['id_theme'], $theme['theme_dir'], $theme['theme_name']);
	}

	public function getDefault()
	{
		return $this->getById($this->_app['settings']['default_theme']);
	}

	public function getPathForId($id)
	{
	}
}