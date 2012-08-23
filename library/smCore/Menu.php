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
	protected $_app;

	protected $_parents = array();
	protected $_menu = array();
	protected $_active = array();

	public function __construct(Application $app)
	{
		$this->_app = $app;

		$this->_app['lang']->loadPackagesByType('menu');

		$cache = $this->_app['cache'];

		if (false === $this->_parents = $cache->load('core_menu_rows'))
		{
			$db = $this->_app['db'];

			$result = $db->query("
				SELECT *
				FROM {db_prefix}menu
				ORDER BY menu_order ASC");

			// Pack these up into a more usable format
			$this->_parents = array();

			while ($row = $result->fetch())
			{
				$this->_parents[$row['menu_parent']][] = $row;
			}

			$cache->save('core_menu_rows', $this->_parents);
		}

		$this->_buildMenu($this->_menu);
	}

	/**
	 * Set the active menu item URLs.
	 *
	 * @param null|false|string For each parameter passed, null skips setting that level, false removes
	 *                          the active URL for that level, and anything else sets that level.
	 */
	public function setActive()
	{
		$args = func_get_args();

		foreach ($args as $level => $arg)
		{
			if ($arg === false)
			{
				unset($this->_active[$level]);
			}
			else if ($arg !== null)
			{
				$this->_active[$level] = (string) $arg;
			}
		}
	}

	/**
	 * 
	 *
	 * @return array
	 */
	public function getMenu()
	{
		$this->_markActive($this->_menu);

		return $this->_menu;
	}

	/**
	 * 
	 *
	 * @param array &$parent
	 * @param int   $id
	 */
	protected function _buildMenu(&$parent, $id = 0)
	{
		if (!empty($this->_parents[$id]))
		{
			foreach ($this->_parents[$id] as $item)
			{
				if ($item['menu_visible'] && (empty($item['menu_permission']) || $this->_app['user']->hasPermission($item['menu_permission'])))
				{
					$parent[$item['menu_name']] = array(
						'url' => $this->_app['settings']['url'] . $item['menu_url'],
						'title' => $this->_app['lang']->get($item['menu_title']),
						'submenu' => array(),
						'active' => false,
					);

					$this->_buildMenu($parent[$item['menu_name']]['submenu'], $item['id_menu']);
				}
			}
		}
	}

	/**
	 * 
	 *
	 * @param array &$menu
	 * @param int   $level
	 */
	protected function _markActive(&$menu, $level = 0)
	{
		if (!empty($this->_active[$level]) && isset($menu[$this->_active[$level]]))
		{
			$menu[$this->_active[$level]]['active'] = true;

			$this->_markActive($menu[$this->_active[$level]]['submenu'], $level + 1);
		}
	}
}