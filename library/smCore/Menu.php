<?php

/**
 * smCore Menu Class
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

class Menu
{
	protected static $_menu = array();

	protected static $_primary;
	protected static $_secondary;
	protected static $_tertiary;

	public static function getMenu()
	{
		$cache = Application::get('cache');

//		if (($menu_rows = $cache->load('core_menu_rows')) === null)
		{
			$db = Application::get('db');

			$result = $db->query("
				SELECT *
				FROM beta_menu
				ORDER BY menu_order ASC");

			// Pack these up into a more usable format
			$menu_rows = array();

			while ($row = $result->fetch())
				$menu_rows[$row->menu_parent][] = $row;

			$cache->save($menu_rows, 'core_menu_rows');
		}

		$user = Application::get('user');
		$menu = array();

		// @todo: make this a recursive function
		if (!empty($menu_rows[0]))
		{
			foreach ($menu_rows[0] as $primary)
			{
				if ($primary->menu_visible && self::_canAccess($user, $primary))
				{
					$menu[$primary->id_menu] = array(
						'url' => Settings::URL . $primary->menu_url,
						'title' => Application::get('lang')->get($primary->menu_title),
						'submenu' => array(),
						'active' => $primary->menu_url === self::$_primary,
					);

					if (!empty($menu_rows[$primary->id_menu]))
					{
						foreach ($menu_rows[$primary->id_menu] as $secondary)
						{
							if ($secondary->menu_visible && self::_canAccess($user, $secondary))
							{
								$menu[$primary->id_menu]['submenu'][$secondary->id_menu] = array(
									'url' => Settings::URL . $secondary->menu_url,
									'title' => Application::get('lang')->get($secondary->menu_title),
									'submenu' => array(),
									'active' => $secondary->menu_url === self::$_secondary,
								);
							}
						}
					}
				}
			}
		}

		return $menu;
		
	}

	public static function setActive($primary = null, $secondary = null, $tertiary = null)
	{
		if ($primary !== null)
			self::$_primary = $primary;

		if ($secondary !== null)
			self::$_secondary = $secondary;

		if ($tertiary !== null)
			self::$_tertiary = $tertiary;
	}

	protected static function _canAccess($user, $menu_item)
	{
		if (empty($menu_item->menu_permission))
			return true;

		return $user->hasPermission($menu_item->menu_permission);
	}
}