<?php

/**
 * smCore Menu Item Class
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

use ArrayAccess, ArrayIterator, IteratorAggregate;

class MenuItem implements ArrayAccess, IteratorAggregate
{
	protected $_name;
	protected $_title;
	protected $_visible;
	protected $_url;
	protected $_order;
	protected $_active = false;

	protected $_children = array();

	public function __construct($name, $title, $url, $visible = true, $order = 50)
	{
		$this->_name = $name;
		$this->_title = $title;
		$this->_url = $url;
		$this->_visible = (bool) $visible;
		$this->_order = (int) $order;
	}

	public function setName($name)
	{
		$this->_name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function setTitle($title)
	{
		$this->_title = $title;

		return $this;
	}

	public function getTitle()
	{
		return $this->_title;
	}

	public function setUrl($url)
	{
		$this->_url = $url;

		return $this;
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function setVisible($visible)
	{
		$this->_visible = (bool) $visible;

		return $this;
	}

	public function getVisible()
	{
		return $this->_visible;
	}

	public function setOrder($order)
	{
		$this->_order = (int) $order;

		return $this;
	}

	public function getOrder()
	{
		return $this->_order;
	}

	public function setActive($active, $children)
	{
		$this->_active = (bool) $active;

		if (is_array($children) && !empty($children))
		{
			$current = array_shift($children);

			if (!empty($current) && isset($this->_children[$current]))
			{
				$this->_children[$current]->setActive(true, $children);
			}
		}

		return $this;
	}

	public function isActive()
	{
		return $this->_active;
	}

	public function addItem(MenuItem $item)
	{
		$this->_children[$item->getName()] = $item;
	}

	public function removeItem($name)
	{
		unset($this->_children[$name]);

		return $this;
	}

	public function hasChildren()
	{
		return !empty($this->_children);
	}

	public function offsetGet($offset)
	{
		if (isset($this->_children[$offset]))
		{
			return $this->_children[$offset];
		}

		return null;
	}

	public function offsetSet($offset, $value)
	{
		throw new Exception('Please use MenuItem::addItem to add items to the menu.');
	}

	public function offsetUnset($offset)
	{
		unset($this->_children[$offset]);
	}

	public function offsetExists($offset)
	{
		return isset($this->_children[$offset]);
	}

	public function getIterator()
	{
		$items = $this->_children;

		// Copy the array because the keys will be overwritten
		usort($items, function($a, $b)
		{
			if ($a->getOrder() === $b->getOrder())
			{
				return 0;
			}

			return $a->getOrder() > $b->getOrder() ? 1 : -1;
		});

		return new ArrayIterator($items);
	}
}