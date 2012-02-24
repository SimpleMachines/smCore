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
	protected $_controls = array();

	protected $_properties = array();
	protected $_requiredProperties = array('action', 'method');

	public function __construct(array $properties = array())
	{
		$this->_validateProperties($properties);
		$this->_properties = $properties;

		if (!empty($properties['controls']))
		{
			foreach ($properties['controls'] as $name => $control)
				$this->addControl($name, $control);

			unset($this->_properties['controls']);
		}
	}

	public function addControl($name, $control)
	{
		$this->_validateControl($control, $name);
		$this->_controls[$name] = $control;

		$this->_controls[$name]->setProperty('name', $name);

		if ($this->_controls[$name]->getProperty('id') === null)
			$this->_controls[$name]->setProperty('id', $name);
	}

	public function getControl($name)
	{
		if (isset($this->_controls[$name]))
			return $this->_controls[$name];

		return false;
	}

	public function removeControl($name)
	{
		if (isset($this->_controls[$name]))
			unset($this->_controls[$name]);
	}

	public function getControls()
	{
		return $this->_controls;
	}

	public function setProperty($name, $value)
	{
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

	public function getContext()
	{
		$context = array(
			'attributes' => array(
				'action' => $this->_properties['action'],
				'method' => $this->_properties['method'],
			),
			'controls' => array(),
			'show_reset' => !empty($this->_properties['show_reset']),
		);

		if (!empty($this->_properties['id']))
			$context['attributes']['id'] = $this->_properties['id'];

		foreach ($this->_controls as $control)
		{
			$context['controls'][] = $control->getContext();

			// Add the enctype attribute so that the file(s) can be submitted correctly
			if ($control->type === 'file')
				$context['attributes']['enctype'] = 'multipart/form-data';
		}

		return $context;
	}

	public function getValues()
	{
		$values = array();

		$errors = array();
		$error_controls = array();

		// Validate as we go
		foreach ($this->_controls as $control)
		{
			// If the validation function doesn't return true, store the error it returns.
			if (($error = $control->validate($this)) !== true)
			{
				if (!empty($error))
					$errors[] = $error;

				$error_controls[] = $error['name'];
			}
			else
				$values[$control->getProperty('name')] = $control->getValue();
		}

		if (empty($errors))
			return $values;
		else
		{
			/**
			 * @todo Load a "form errors" template and display all errors?
			 *       Reload the form and show the errors there?
			 *       Maybe make this a form property?
			 */
			die(var_dump($errors));
		}
	}

	public function display()
	{
		Application::$theme->addNamespace('forms', 'com.fustrate.forms');
		Application::$theme->loadTemplates('forms');
		Application::$theme->addTemplate('form', 'forms');
		Application::$context['form'] = $this->getContext();
	}

	protected function _validateProperties($properties)
	{
		if (!empty($this->_requiredProperties))
			foreach ($this->_requiredProperties as $property)
				if (!array_key_exists($property, $properties))
					throw new Exception(array('exceptions.form.missing_property', $property));
	}

	protected function _validateControl(Control $control, $name)
	{
	}
}