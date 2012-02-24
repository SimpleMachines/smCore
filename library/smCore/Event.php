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
use smCore\Event\Dispatcher;

class Event
{
	protected $owner;
	protected $name;
	protected $arguments;
	protected $value = null;
	protected $fired = false;

	public function __construct($owner, $name, $arguments = array())
	{
		$this->owner = $owner;
		$this->name = $name;

		if (!is_array($arguments))
			$arguments = array($arguments);

		$this->arguments = $arguments;
	}

	public function getOwner()
	{
		return $this->owner;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getArguments()
	{
		return $this->arguments;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value)
	{
		$this->value = $value;
	}

	public function fired()
	{
		return $this->fired;
	}

	// Just a shortcut to let the dispatcher know to wake up and do something
	public function fire()
	{
		Dispatcher::fire($this);
		$this->fired = true;
	}
}