<?php

/**
 * smCore CSRF Form Control Class
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

class CSRF extends Control
{
	public $type = 'csrf';
	protected $_requiredProperties = array('key');

	public function getContext()
	{
		return array(
			'name' => 'csrf_token',
			'value' => $this->_createToken(),
			'__type' => $this->type,
		);
	}

	public function getValue()
	{
		return Application::get('input')->post->getAlnum('csrf_token');
	}

	public function validate($form)
	{
		return $this->getValue() === $this->_createToken();
	}

	protected function _createToken()
	{
		return md5(md5(Application::$visitor['token'] . $this->_properties['key']));
	}
}