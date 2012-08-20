<?php

/**
 * smCore Event Dispatcher Class
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

class EventDispatcher
{
	protected $_listeners = array();

	/**
	 * Creates a new EventDispatcher.
	 */
	public function __construct()
	{
	}

	/**
	 * Sets the listeners this event dispatcher should know about.
	 *
	 * @param array   $listeners
	 * @param boolean $overwrite
	 */
	public function addListeners(array $listeners, $overwrite = false)
	{
		if ($overwrite)
		{
			$this->_listeners = array();
		}

		foreach ($listeners as $listener)
		{
			$this->_listeners[$listener['listener_name']][] = $listener['callback'];
		}
	}

	/**
	 * Adds a listener to this dispatcher.
	 *
	public function addListener($name, $callback)
	{
		$this->_listeners[$name][] = $callback;
	}

	/**
	 * Fires an event
	 *
	 * @param \smCore\Event $event
	 *
	 * @return 
	 */
	public function fire(Event $event)
	{
		$name = $event->getName();

		if (empty($this->_listeners[$name]))
		{
			return;
		}

		$event->setDispatcher($this);

		foreach ($this->_listeners[$name] as $listener)
		{
			if (is_callable($listener))
			{
				$result = call_user_func($listener, $event);

				// An event sequence can be interrupted by returning a non-null value
				if ($result !== null)
				{
					return $result;
				}
			}
		}
	}

	public static function getListeners($name = null)
	{
		if (null === $name)
		{
			return $this->_listeners;
		}

		if (empty($this->_listeners[$name]))
		{
			return array();
		}

		return $this->_listeners[$name];
	}
}