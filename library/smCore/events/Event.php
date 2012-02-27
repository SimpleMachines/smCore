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
use smCore\Application;

/**
 * Event and event listeners.
 * An event is 'fired' by the sub-system when particular points in code are reached.
 * Modules can register themselves (with a callback) for the events of the platform, and be notified when
 * they happen, allowing them to add/change/execute custom code.
 */
class Event
{
	protected $owner;
	protected $name;
	protected $arguments;
	protected $value = null;
	protected $fired = false;

	/**
	 * Make an event.
	 *
	 * @param $owner
	 * @param $name
	 * @param array $arguments
	 */
	public function __construct($owner, $name, $arguments = array())
	{
		$this->owner = $owner;
		$this->name = $name;

		if (!is_array($arguments))
			$arguments = array($arguments);

		$this->arguments = $arguments;
	}

	/**
	 * Retrieve the owner of this event.
	 *
	 * @return mixed
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * Get the event's name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the arguments.
	 *
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * Get the value
	 *
	 * @return null
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Set the value.
	 *
	 * @param $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Whether this event was fired already.
	 *
	 * @return bool
	 */
	public function fired()
	{
		return $this->fired;
	}

	/**
	 * Just a shortcut to let the dispatcher know to wake up and do something.
	 */
	public function fire()
	{
		EventDispatcher::fire($this);
		$this->fired = true;
	}
}