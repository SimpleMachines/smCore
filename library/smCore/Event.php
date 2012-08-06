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

class Event
{
	protected $_owner;
	protected $_name;
	protected $_arguments;
	protected $_value = null;
	protected $_fired = false;

	protected $_dispatcher;

	public function __construct($owner, $name, $arguments = null)
	{
		$this->_owner = $owner;
		$this->_name = $name;
		$this->_arguments = $arguments;
	}

	public function setDispatcher(EventDispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}

	public function getOwner()
	{
		return $this->_owner;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function getArguments()
	{
		return $this->_arguments;
	}

	public function getValue()
	{
		return $this->_value;
	}

	public function setValue($value)
	{
		$this->_value = $value;

		return $this;
	}

	public function fired()
	{
		return $this->_fired;
	}

	// Just a shortcut to let the dispatcher know to wake up and do something
	public function fire()
	{
		EventDispatcher::fire($this);
		$this->_fired = true;

		return $this;
	}
}