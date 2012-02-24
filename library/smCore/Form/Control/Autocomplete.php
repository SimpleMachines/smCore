<?php

/**
 * smCore Autocomplete Form Control Class
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
use smCore\Application, smCore\Form\Control;

class Autocomplete extends Control
{
	public $type = 'autocomplete';
	protected $_requiredProperties = array('source');

	protected $_defaults = array(
		'value' => array(),
		'validation' => array(
			'required' => false,
		),
		'limit' => 1,
		'require' => 0,
		'allow_new' => false,
	);

	public function getValue()
	{
		$input = Application::get('input');

		$values = array(
			'new' => array(),
			'existing' => array(),
		);

		if ($this->_properties['allow_new'])
		{
			$raw = $input->post->getAlnum($this->_properties['name'] . '_new');
			if (!empty($raw))
				foreach ($raw as $value)
					if (!empty($value))
						$values['new'][] = $value;
		}

		$raw = $input->post->getAlnum($this->_properties['name'] . '_value');

		if (!empty($raw))
			foreach ($raw as $value)
				if (!empty($value))
					$values['existing'][] = $value;

		return $values;
	}
}