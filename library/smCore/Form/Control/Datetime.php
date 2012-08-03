<?php

/**
 * smCore Datetime Form Control Class
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
use smCore\Application, smCore\Form\Control, smCore\Utility;

class Datetime extends Control
{
	public $type = 'datetime';

	public function getValue()
	{
		$input = Application::get('input');

		$date = strtolower(trim($input->post->getRaw($this->_properties['name'] . '_date')));
		$time = strtolower(trim($input->post->getRaw($this->_properties['name'] . '_time')));

		return Utility::getTimestamp($date, $time);
	}

	public function validate($form)
	{
		$value = $this->getValue();

		if ($value === false)
			return false;

		if (!empty($this->_properties['validation']['after']) && $value < $this->_properties['validation']['after'])
			return Application::get('lang')->get('form_errors_datetime_after', array($this->_properties['label'], date('n/j/Y g:i:s A', $this->_properties['validation']['after'])));

		if (!empty($this->_properties['validation']['before']) && $value > $this->_properties['validation']['before'])
			return Application::get('lang')->get('form_errors_datetime_before', array($this->_properties['label'], date('n/j/Y g:i:s A', $this->_properties['validation']['before'])));

		if (!empty($this->_properties['validation']['after_control']))
		{
			$other = $form->getControl($this->_properties['validation']['after_control']);

			if ($value <= $other->getValue())
				return Application::get('lang')->get('form_errors_datetime_after', array($this->_properties['label'], $other->getProperty('label')));
		}

		if (!empty($this->_properties['validation']['before_control']))
		{
			$other = $form->getControl($this->_properties['validation']['before_control']);

			if ($value >= $other->getValue())
				return Application::get('lang')->get('form_errors_datetime_before', array($this->_properties['label'], $other->getProperty('label')));
		}

		return parent::validate($form);
	}
}