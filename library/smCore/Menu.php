<?php

/**
 * smCore platform
 *
 * @package smCore
 * @author Fustrate
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
use smCore\model\User, smCore\Exception;

/**
 * Menu manager class. It represents a Menu item (hmm) and it should hold a composite of items.
 * A menu can have a submenu, which is a Menu instance as well.
 * The class has currently a flag, $_useStaticMenu, to specify whether it loads menu items as instances
 * of Menu, or as array with menu information.
 */
class Menu
{
	/**
	 * Experimental variable: use arrays of menu data,
	 * as opposed to a composite of Menu items objects.
	 * @var bool
	 */
	private static $_useStaticMenu = false;
	private static $_menu = null;

	private $_title = null;
	private $_badge = null;
	private $_submenu = null;
	private $_header = null;
	private $_order = 0;
	private $_link = null;

	/**
	 * Create a menu item, from the given parameters.
	 *
	 * @param $title
	 * @param $header
	 * @param $link
	 * @param int $order
	 * @param null $badge
	 * @param null $submenu
	 */
	private function __construct($title, $header, $link, $order = 0, $badge = null, $submenu = null)
	{
		// check received params and set them up accordingly
		$this->_title = $title;
		$this->_header = $header;
		$this->_link = $link;
		$this->_order = $order;
		$this->_badge = $badge;
		(!empty($submenu) && (is_array($submenu))) ? $this->_submenu = $submenu : (!empty($submenu) ? $this->_submenu = array($submenu) : null);
	}

	/**
	 * Retrieve the menu...
	 *
	 * @static
	 * @return array
	 */
	static function getMenu()
	{
		if (self::$_menu === null)
			self::setupMenu(self::$_menu, Router::getRoutes());

		return self::$_menu['menu'];
	}

	/**
	 * Grab the currently active submenu as array, such as for themes which only show the current submenu.
	 *
	 * @return array
	 */
	static function getSubmenu()
	{
		foreach (self::$_menu['menu'] as $url => $info)
			if ($info['active'] && count($info['menu']) > 1)
				return $info['menu'];
	}

	/**
	 * Retrieve the current action, associated with the active menu item.
	 *
	 * @static
	 * @return int|string
	 */
	static function getAction()
	{
		foreach (self::$_menu['menu'] as $url => $info)
			if ($info['active'])
				return $url;
	}

	/**
	 * Retrieve the active subaction of the current action.
	 * (null if none)
	 *
	 * @static
	 * @return string|null
	 */
	static function getSubaction()
	{
		$action = self::getAction();

		if (empty($action))
			return null;

		foreach (self::$_menu['menu'][$action] as $url => $info)
			if ($info['active'])
				return $url;
	}

	/**
	 * Setup menu.
	 *
	 * @param array &$parent
	 * @param array $routes
	 * @param bool $useStaticMenu
	 */
	static function setupMenu(&$parent, $routes, $useStaticMenu = false)
	{
		self::$_useStaticMenu = $useStaticMenu;
		if (!empty($routes))
		{
			foreach ($routes as $url => $route)
			{
				if (!empty($route['resource']) && !User::getCurrentUser()->isAllowed($route['resource'][0], $route['resource'][1]))
					continue;

				if (self::_isVisible($route))
				{
					if (self::$_useStaticMenu)
					{
						$menu['menu'][$url] = array(
							'title' => !empty($route['title']) ? Language::getLanguage()->get(array('menu_titles', $route['title'])) : '',
							'badge' => null,
                            'order' => !empty($route['order']) ? $route['order'] : 50,
							'header' => !empty($route['header']) ? Language::getLanguage()->get(array('menu_titles', $route['header'])) : null,
                            'active' => !empty($route['active']),
							'menu' => array(),							
						);

					if (!empty($route['routes']))
						self::setupMenu($parent['menu'][$url], $route['routes']);					}
					else
					{
						self::addMenu(
							!empty($route['title']) ? Language::getLanguage()->get(array('menu_titles', $route['title'])) : '',
							!empty($route['header']) ? Language::getLanguage()->get(array('menu_titles', $route['header'])) : null,
							$url,
							!empty($route['order']) ? $route['order'] : 50
						);

						// @todo fixme
					if (!empty($route['routes']))
						self::setupMenu($parent['menu'][$url], $route['routes']);					}
				}
			}

			if (!empty($parent['menu']))
			{
				uasort($parent['menu'], function($a, $b)
				{
					if ($a['order'] == $b['order'])
						return 0;

					return ($a['order'] > $b['order']) ? 1 : -1;
				});
			}
		}
	}

	/**
	 * Add an item to the menu. It adds it as a new Menu instance.
	 *
	 * @static
	 * @param $title
	 * @param $header
	 * @param $link
	 * @param int $order
	 * @param null $badge
	 * @param null $submenu
	 * @return Menu
	 */
	static function addMenu($title, $header, $link, $order = 0, $badge = null, $submenu = null)
	{
		// check params and throw exception if unsuitable.
		// create the item, add it to the menu, and return the newly created item otherwise.
		$menuItem = new self($title, $header, $link, $order, $badge, $submenu);
		$menu[$link] = $menuItem;
		return $menuItem;
	}

	/**
	 * Remove from the menu, the item with link $link.
	 *
	 * @static
	 * @param $link
	 */
	static function removeMenu($link)
	{
		if (!empty(self::$_menu) && array_key_exists($link, self::$_menu))
			unset(self::$_menu[$link]);
	}

	/**
	 * Whether a route is not visible, or the user cannot access it.
	 *
	 * @static
	 * @param $route
	 * @return bool
	 * @throws Exception
	 */
    protected static function _isVisible($route)
	{
		// Don't show things that they can't access, or if visibility isn't explicitly set
		if (!self::_isAccessible($route) || empty($route['visible']))
			return false;

		if (is_bool($route['visible']))
			return $route['visible'];

		if (!empty($route['visible']['callback']))
		{
			if (!is_callable($route['visible']['callback']))
				throw new Exception('exceptions.router.visibility_callback_invalid');

			if (call_user_func($route['visible']['callback']) !== true)
				return false;
		}

		return true;
	}

	/**
	 * Whether the current user can access a particular route.
	 * (@todo this doesn't belong here.)
	 *
	 * @static
	 * @param $route
	 * @return bool
	 * @throws Exception
	 */
	protected static function _isAccessible($route)
	{
		if (!array_key_exists('access', $route))
			return true;

		if ($route['access'] === false)
			return false;

		if (!empty($route['access']['resource']))
		{
			if (!User::getCurrentUser()->isAllowed($route['access']['resource'][0], $route['access']['resource'][1]))
				return false;
		}

		if (!empty($route['access']['callback']))
		{
			if (!is_callable($route['access']['callback']))
				throw new Exception('exceptions.router.access_callback_invalid');

			if (call_user_func($route['access']['callback']) !== true)
				return false;
		}

		return true;
	}
}