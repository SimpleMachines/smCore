<?php

/**
 * smCore Event Class
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

use ArrayAccess, IteratorAggregate, Countable, ArrayIterator;

class Event implements ArrayAccess, IteratorAggregate
{
	protected $name;
	protected $data;

	protected $stopped = false;
	protected $fired = false;

	protected $dispatcher;

	public function __construct($name, array $data = array())
	{
		$this->name = $name;
		$this->data = $data;
	}

	public function setDispatcher(EventDispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;

		return $this;
	}

	public function getDispatcher()
	{
		return $this->dispatcher;
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function hasFired($firing = false)
	{
		if ($firing)
		{
			$this->fired = true;
		}

		return $this->fired;
	}

	/**
	 * Check if a listener has asked for this event to stop being sent to listeners.
	 *
	 * @return boolean True if propagation is stopped, false otherwise
	 */
	public function isPropagationStopped()
	{
		return $this->stopped;
	}

	/**
	 * Stop any further listeners from being notified of this event.
	 *
	 * @return self
	 */
	public function stopPropagation()
	{
		$this->stopped = true;

		return $this;
	}

	/**
	 * ArrayAccess method for checking for the existence of an event argument.
	 *
	 * @param mixed $offset The offset the check
	 *
	 * @return boolean True if the offset exists, false otherwise
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * ArrayAccess method for setting an event argument via array syntax.
	 *
	 * @param mixed $offset The offset to set the value for
	 * @param mixed $value  The value to set
	 */
	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	/**
	 * ArrayAccess method for getting an event argument via array syntax.
	 *
	 * @param mixed $offset The offset to search for and return
	 *
	 * @return mixed The argument value at the provided offset, null if not found
	 */
	public function offsetGet($offset)
	{
		if (isset($this->data[$offset]))
		{
			return $this->data[$offset];
		}

		return null;
	}

	/**
	 * ArrayAccess method for unsetting an event argument.
	 *
	 * @param mixed $offset The offset to attempt to unset
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	/**
	 * ArrayIterator interface method
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->data);
	}
}