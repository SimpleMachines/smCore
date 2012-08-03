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

namespace smCore\Event;

use smCore\Application, smCore\Event;

class Dispatcher
{
	protected static $_listeners = array();

	public function __construct()
	{
		if (false === self::$_listeners = Application::get('cache')->load('core_event_listeners'))
		{
			self::recompile();
		}
	}

	private function __clone(){}

	public static function recompile()
	{
		self::$_listeners = array();
		$modules = Application::get('modules');
		$identifiers = $modules->getIdentifiers();

		foreach ($identifiers as $identifier)
		{
			$config = $modules->getModuleConfig($identifier);

			// Doesn't have any listeners, so skip it
			if (empty($config['events']))
			{
				continue;
			}

			foreach ($config['events'] as $name => $listener)
			{
				if (empty($listener['enabled']))
				{
					continue;
				}

				self::$_listeners[$name][] = $listener['callback'];
			}
		}

		Application::get('cache')->save('core_event_listeners', self::$_listeners, array('dependency_module_registry'));
	}

	public static function fire(Event $event)
	{
		$name = $event->getName();

		if (empty(self::$_listeners[$name]))
		{
			return;
		}

		foreach (self::$_listeners[$name] as $listener)
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
			return self::$_listeners;
		}

		if (empty(self::$_listeners[$name]))
		{
			return array();
		}

		return self::$_listeners[$name];
	}
}