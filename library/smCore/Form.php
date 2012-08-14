<?php

/**
 * smCore Form Class
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

use smCore\Form\Control;

class Form
{
	protected $_attributes = array();
	protected $_controls = array();

	public function __construct($action, array $controls = array())
	{
		$this->_attributes = array(
			'action' => $action,
			'method' => 'post',
		);

		$this->addControls($controls);
	}

	public function addControls(array $controls)
	{
		foreach ($controls as $name => $control)
		{
			$this->addControl($name, $control);
		}

		return $this;
	}

	public function addControl($name, $control)
	{
		$this->_controls[$name] = $control;

		return $this;
	}

	public function getControl($name)
	{
		if (isset($this->_controls[$name]))
		{
			return $this->_controls[$name];
		}

		return null;
	}

	public function setAttribute($name, $value)
	{
		$this->_attributes[$name] = $value;

		return $this;
	}

	public function getAttribute($name)
	{
		if (isset($this->_attributes[$name]))
		{
			return $this->_attributes['name'];
		}

		return null;
	}

	public function setAttributes(array $attributes)
	{
		foreach ($attributes as $name => $value)
		{
			$this->setAttribute($name, $value);
		}

		return $this;
	}

	public function getAttributes()
	{
		return $this->_attributes;
	}

	public function getControls()
	{
		return $this->_controls;
	}
}