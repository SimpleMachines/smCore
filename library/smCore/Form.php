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









class Form2
{
	protected $_control_sections = array();

	protected $_properties = array();
	protected $_requiredProperties = array('action', 'method');

	public function __construct(array $properties = array())
	{
		$this->_validateProperties($properties);
		$this->_properties = $properties;
		$section = 0;

		if (!empty($properties['controls']))
		{
			foreach ($properties['controls'] as $name => $control)
			{
				if (is_array($control))
				{
					foreach ($control as $real_name => $real_control)
					{
						if ($real_name === 0)
						{
							$section = $real_control;
						}
						else
						{
							$this->addControl($real_name, $real_control, $section);
						}
					}
				}
				else
				{
					$this->addControl($name, $control);
				}
			}

			unset($this->_properties['controls']);
		}
	}

	public function addControl($name, $control, $section = 0)
	{
		$this->_validateControl($control, $name);
		$this->_control_sections[$section][$name] = $control;

		$this->_control_sections[$section][$name]->setProperty('name', $name);

		if (null === $this->_controls[$section][$name]->getProperty('id'))
		{
			$this->_control_sections[$section][$name]->setProperty('id', $name);
		}
	}

	public function getControl($name)
	{
		foreach ($this->_control_sections as $section => $controls)
		{
			if (isset($this->_control_sections[$section][$name]))
			{
				return $this->_controls[$name];
			}
		}

		return false;
	}

	public function removeControl($name)
	{
		if (isset($this->_controls[$name]))
		{
			unset($this->_controls[$name]);
		}
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
		{
			return $this->_properties[$name];
		}

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
		{
			$context['attributes']['id'] = $this->_properties['id'];
		}

		foreach ($this->_controls as $control)
		{
			$context['controls'][] = $control->getContext();

			// Add the enctype attribute so that the file(s) can be submitted correctly
			if ($control->type === 'file')
			{
				$context['attributes']['enctype'] = 'multipart/form-data';
			}
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
				{
					$errors[] = $error;
				}

				$error_controls[] = $error['name'];
			}
			else
			{
				$values[$control->getProperty('name')] = $control->getValue();
			}
		}

		if (empty($errors))
		{
			return $values;
		}
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

	protected function _validateProperties($properties)
	{
		if (!empty($this->_requiredProperties))
		{
			foreach ($this->_requiredProperties as $property)
			{
				if (!array_key_exists($property, $properties))
				{
					throw new Exception(array('exceptions.form.missing_property', $property));
				}
			}
		}
	}

	protected function _validateControl(Control $control, $name)
	{
	}









	public function getAction()
	{
		return $this->_properties['action'];
	}

	public function getMethod()
	{
		return $this->_properties['method'];
	}





	public function getIterator()
	{
		return new ArrayIterator($this->_controls);
	}
}