<?php

/**
 * smCore platform
 *
 * @package smCore
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
 */

namespace smCore\events;
use smCore\Application, smCore\ModuleRegistry;

/**
 * Event dispatcher.
 * This is the main entry point of the events sub-system. It takes care of events, keeping an up to date list of
 * the events defined by modules, and their listeners, and makes sure to notify the listeners when the event is
 * fired.
 */
class EventDispatcher
{
	protected static $_instance = null;
	protected static $_listeners = array();

	/**
	 * Singleton pattern: unique instance of EventDispatcher.
	 *
	 * @static
	 * @return \smCore\events\EventDispatcher
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * Create the unique EventDispatcher object.
	 */
	private function __construct()
	{
		// load from cache
		self::recompile();
	}

	/**
	 * Singleton pattern: no clone.
	 */
	private function __clone(){}

	/**
	 * Recompile method is "compiling" the events from each module, updating EventDispatcher's internal list.
	 *
	 * @static
	 */
	static function recompile()
	{
		self::$_listeners = array();
		$modules = ModuleRegistry::getInstance()->getLoadedModules();

		foreach ($modules as $module)
		{
			// Doesn't have any listeners, so skip it
			if ($module->getEvents() == array())
				continue;

			foreach ($module->getEvents() as $name => $listener)
			{

				if (empty($listener['enabled']))
					continue;

				self::$_listeners[$name][] = $listener['callback'];
			}
		}
	}

	/**
	 * Fire event: notify listeners by calling their callback method.
	 *
	 * @static
	 * @param Event $event
	 * @return mixed
	 */
	static function fire(Event $event)
	{
		$name = $event->getName();

		if (empty(self::$_listeners[$name]))
			return;

		foreach (self::$_listeners[$name] as $listener)
			if (is_callable($listener))
			{
				$result = call_user_func($listener, $event);

				// An event sequence can be interrupted by returning a non-null value
				if ($result !== null)
					return $result;
			}
	}

	/**
	 * Retrieve the list of listeners.
	 *
	 * @static
	 * @param string $name=null
	 * @return array
	 */
	static function getListeners($name = null)
	{
		if ($name === null)
			return self::$_listeners;

		if (empty(self::$_listeners[$name]))
			return array();

		return self::$_listeners[$name];
	}

	/**
	 * Remove the listeners for an event.
	 *
	 * @static
	 * @param string $name=null
	 */
	static function removeListeners($name = null)
	{
		if ($name === null)
			self::$_listeners = array();
		elseif (!empty(self::$_listeners[$name]))
			self::$_listeners[$name] = array();
	}

	/**
	 * Add a listener to the internal list of listeners for a particular event.
	 *
	 * @static
	 * @param $name
	 * @param $listener
	 */
	static function addListener($name, $listener)
	{
		if (!empty($name) && !empty($listener) && !empty($listener['enabled']))
		{
			if (empty(self::$_listeners[$name]))
				self::$_listeners[$name] = array();
			self::$_listeners[$name][] = $listener['callback'];
		}
	}
}