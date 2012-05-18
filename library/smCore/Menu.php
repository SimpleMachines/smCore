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
	protected $_parents = array();
	protected $_menu = array();
	protected $_active = array();

	protected static $_primary;
	protected static $_secondary;
	protected static $_tertiary;

	public function __construct()
	{
		Application::get('lang')->loadPackagesByType('menu');


		$cache = Application::get('cache');

//		if (($this->_parents = $cache->load('core_menu_rows')) === null)
		{
			$db = Application::get('db');

			$result = $db->query("
				SELECT *
				FROM beta_menu
				ORDER BY menu_order ASC");

			// Pack these up into a more usable format
			$this->_parents = array();

			while ($row = $result->fetch())
				$this->_parents[$row->menu_parent][] = $row;

			$cache->save($this->_parents, 'core_menu_rows');
		}

		$this->_buildMenu($this->_menu);
	}

	protected function _buildMenu(&$parent, $parent_id = 0, $level = 0)
	{
		if (!empty($this->_parents[$parent_id]))
		{
			foreach ($this->_parents[$parent_id] as $item)
			{
				if ($item->menu_visible && $this->_canAccess($item))
				{
					$parent[$item->id_menu] = array(
						'url' => Settings::URL . $item->menu_url,
						'title' => Application::get('lang')->get($item->menu_title),
						'level' => $level,
						'menu' => array(),
					);

					$this->_buildMenu($parent[$item->id_menu]['menu'], $item->id_menu, $level + 1);
				}
			}
		}
	}

	/**
	 * Set the active menu item URLs.
	 *
	 * @param null|false|string For each parameter passed, null skips setting that level, false removes the active URL for that level, and anything else sets that level.
	 */
	public function setActiveItems()
	{
		$args = func_get_args();

		foreach ($args as $level => $arg)
		{
			if ($arg === false)
				unset($this->_active[$level]);
			else if ($arg !== null)
				$this->_active[$level] = (string) $arg;
		}

		// @todo LEGACY - REMOVE
		if (isset($args[0]))
			self::$_primary = $args[0];

		if (isset($args[1]))
			self::$_secondary = $args[1];

		if (isset($args[2]))
			self::$_tertiary = $args[2];
	}

	public function getMenu()
	{
		$menu = array();

		$menu_rows = $this->_parents;

		// @todo: make this a recursive function
		if (!empty($menu_rows[0]))
		{
			foreach ($menu_rows[0] as $primary)
			{
				if ($primary->menu_visible && $this->_canAccess($primary))
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
							if ($secondary->menu_visible && $this->_canAccess($secondary))
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
	}

	protected function _canAccess($menu_item)
	{
		return empty($menu_item->menu_permission) || Application::get('user')->hasPermission($menu_item->menu_permission);
	}

	public function __toString()
	{
		return print_r($this->_menu, true);
	}
}