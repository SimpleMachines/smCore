<?php

/**
 * smCore User Form Control Class
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
use smCore\Application, smCore\Form\Control, ArrayObject;

class User extends Control
{
	public $type = 'user';
	protected $_users = null;

	protected $_defaults = array(
		'value' => array(),
		'validation' => array(
			'required' => false,
		),
		'limit' => 1,
		'require' => 0,
	);

	public function getContext()
	{
		$context = array(
			'type' => $this->type,
		);

		foreach ($this->_properties as $key => $value)
			if ($key !== 'validation')
				$context[$key] = $value;

		return $context;
	}

	// Only returns valid users
	public function getValue()
	{
		// Basic caching, so we don't have to do the db query twice
		if ($this->_users === null)
		{
			$db = Application::get('db');

			$value = Application::get('input')->post->getInt($this->_properties['name'] . '_value');
			$this->_users = array();

			if (empty($value))
				return $this->_users;

			if ($value instanceof ArrayObject)
				$value = $value->getArrayCopy();
			else
				$value = array($value);

			$select = $db->select()
				->from(
					'beta_users',
					array('id_user', 'user_display_name')
				)
				->where('id_user IN (?)', $value);

			$result = $db->query($select);

			if ($result->rowCount() > 0)
				while ($row = $result->fetch())
					$this->_users[$row->id_user] = $row->user_display_name;
		}

		return $this->_users;
	}

	public function validate($form)
	{
		$value = $this->getValue();
		$count = count($this->_users);

		if ($this->_properties['limit'] > 0 && $count > $this->_properties['limit'])
			return Application::get('lang')->get('form_errors_user_exceeded_limit', array($this->_properties['label'], $this->_properties['limit']));

		if ($count < $this->_properties['require'])
			return Application::get('lang')->get('form_errors_user_not_enough', array($this->_properties['label'], $this->_properties['require']));

		return parent::validate($form);
	}
}