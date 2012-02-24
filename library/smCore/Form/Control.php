<?php

/**
 * smCore Form Control Class
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

namespace smCore\Form;
use smCore\Application, smCore\Exception;

abstract class Control
{
	public $type = 'generic';

	protected $_properties = array();
	protected $_requiredProperties = array('label');

	// Stores the value sent by the form - not the initial value!
	protected $_value = null;

	protected $_defaults = array(
		'value' => '',
		'validation' => array(
			'required' => false,
		),
	);

	public function __construct(array $properties = array())
	{
		$this->_validateProperties($properties);
		$this->_properties = array_merge($this->_defaults, $properties);
	}

	protected function _validateProperties($properties)
	{
		if (!empty($this->required_properties))
			foreach ($this->required_properties as $property)
				if (!array_key_exists($property, $properties))
					throw new Exception('exceptions.form.control_missing_property', array($this->type, $property));
	}

	public function setProperty($name, $value)
	{
		if (empty($name))
			return;

		if ($value === null && array_key_exists($name, $this->_properties))
			unset($this->_properties[$name]);
		else
			$this->_properties[$name] = $value;
	}

	public function getProperty($name)
	{
		if (!empty($name) && array_key_exists($name, $this->_properties))
			return $this->_properties[$name];

		return null;
	}

	public function getProperties()
	{
		return $this->_properties;
	}

	// Basic validation - a control's own validate function is called at the end.
	public function validate($form)
	{
		$this->_value = $this->getValue();

		// @todo: filters

		// The actual value of zero is allowed
		if (!empty($this->_properties['validation']['required']) && empty($this->_value) && $this->_value !== "0" && $this->_value !== 0)
			return Application::get('lang')->get('exceptions.form._required', array($this->_properties['label']));

		if (!empty($this->_properties['validation']['minLength']) && mb_strlen($this->_value) < $this->_properties['validation']['minLength'])
			return Application::get('lang')->get('exceptions.form.min_length', array($this->_properties['label']));

		if (!empty($this->_properties['validation']['maxLength']) && mb_strlen($this->_value) > $this->_properties['validation']['maxLength'])
			return Application::get('lang')->get('exceptions.form.max_length', array($this->_properties['label']));

		if (!empty($this->_properties['validation']['regexes']))
		{
			foreach ($this->_properties['validation']['regex'] as $regex)
				if (!preg_match($regex, $this->_value))
					return Application::get('lang')->get('exceptions.form.regex', array($this->_properties['label']));
		}

		if (!empty($this->_properties['validation']['callbacks']))
		{
			foreach ($this->_properties['validation']['callbacks'] as $callback)
			{
				$result = call_user_func_array($callback, array($this, &$this->_value));

				if ($result !== true)
					return $result;
			}
		}

		return true;
	}

	public function getContext()
	{
		$context = array(
			'__type' => $this->type,
		);

		return array_merge($context, $this->_properties);
	}

	public function getValue()
	{
		if ($this->_value === null)
			$this->_value = Application::get('input')->post->getRaw($this->_properties['name']);

		return $this->_value;
	}
}