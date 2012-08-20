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

use ArrayAccess, Iterator, Countable, ArrayObject;

class Event implements ArrayAccess, Iterator, Countable
{
	protected $subject;
	protected $name;
	protected $arguments;
	protected $value = null;
	protected $fired = false;

	protected $dispatcher;

	public function __construct($subject, $name, array $arguments = array())
	{
		$this->subject = $subject;
		$this->name = $name;
		$this->arguments = $arguments;
	}

	public function setDispatcher(EventDispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	public function getSubject()
	{
		return $this->subject;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value)
	{
		$this->value = $value;
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
	 * ArrayAccess method for checking for the existence of an event argument.
	 *
	 * @param mixed $offset The offset the check
	 *
	 * @return boolean True if the offset exists, false otherwise
	 */
	public function offsetExists($offset)
	{
		return isset($this->arguments[$offset]);
	}

	/**
	 * ArrayAccess method for setting an event argument via array syntax.
	 *
	 * @param mixed $offset The offset to set the value for
	 * @param mixed $value  The value to set
	 */
	public function offsetSet($offset, $value)
	{
		$this->arguments[$offset] = $value;
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
		if (isset($this->arguments[$offset]))
		{
			return $this->arguments[$offset];
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
		unset($this->arguments[$offset]);
	}

	/**
	 * Iterator interface method
	 */
	public function rewind()
	{
		reset($this->arguments);
	}

	/**
	 * Iterator interface method
	 *
	 * @return mixed The argument at the current pointer offset
	 */
	public function current()
	{
		return current($this->arguments);
	}

	/**
	 * Iterator interface method
	 *
	 * @return mixed The key of the argument at the current pointer offset
	 */
	public function key()
	{
		return key($this->arguments);
	}

	/**
	 * Iterator interface method
	 *
	 * @return mixed The next argument
	 */
	public function next()
	{
		return next($this->arguments);
	}

	/**
	 * Iterator interface method
	 *
	 * @return boolean True if the argument pointer is valid, false otherwise
	 */
	public function valid()
	{
		return $this->current() !== false;
	}    

	/**
	 * Countable interface method
	 *
	 * @return int The number of arguments in this event
	 */
	public function count()
	{
		return count($this->arguments);
	}
}