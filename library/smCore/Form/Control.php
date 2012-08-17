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

use smCore\Exception;

abstract class Control
{
	protected $_properties = array();

	public function __construct(array $properties = array())
	{
		$this->_properties = array_merge(array(
			'label' => '',
			'name' => '',
			'id' => '',
			'value' => null,
			'help' => null,
			'validation' => array(
				'required' => false,
			),
		), $properties);
	}

	public function getLabel()
	{
		return $this->_properties['label'];
	}

	public function getHelp()
	{
		return $this->_properties['help'];
	}

	public function getValue($from_submit = false, $input = null)
	{
		if ($from_submit && null === $this->_properties['value'])
		{
			$this->_properties['value'] = $input->post->getRaw($this->_properties['name']);
		}

		return $this->_properties['value'];
	}

	public function getName()
	{
		return $this->_properties['name'];
	}

	public function getId()
	{
		return $this->_properties['id'];
	}

	abstract public function getType();
}