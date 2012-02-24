<?php

/**
 * smCore Select Form Control Class
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

class Select extends Control
{
	public $type = 'select';

	public function validate($form)
	{
		$value = $this->getValue();

		// If this wasn't required and they didn't send a real value, it's alright
		if (empty($this->_properties['validation']['required']) && empty($value))
			return true;

		// Otherwise, make sure they sent an approved value
		if (!array_key_exists($this->getValue(), $this->_properties['options']))
			return Application::get('lang')->get('forms_error_invalid_select_value', array($this->_properties['label']));

		return parent::$validate($form);
	}
}