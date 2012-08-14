<?php

/**
 * smCore Form Control Group
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

namespace smCore\Form\Control;

use smCore\Exception, smCore\Form\Control;

class Group extends Control
{
	protected $_properties;
	protected $_controls;

	public function __construct(array $properties = array())
	{
		if (!isset($properties['label']))
		{
			throw new Exception('Control groups must have labels.');
		}

		$this->_properties = array_merge(array(
			'value' => null,
			'label' => null,
			'help' => null,
		), $properties);

		if (!empty($properties['controls']))
		{
			$this->addControls($properties['controls']);
		}
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

	public function getLabel()
	{
		return $this->_properties['label'];
	}

	public function getControls()
	{
		return $this->_controls;
	}

	public function getValue()
	{
		// @todo: loop through controls
	}

	public function getType()
	{
		return 'group';
	}
}