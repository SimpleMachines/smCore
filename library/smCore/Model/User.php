<?php

/**
 * smCore User Model
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

namespace smCore\Model;

use smCore\Application, smCore\Event, smCore\Exception, smCore\Security\Crypt\Bcrypt, smCore\Settings, smCore\Storage;
use ArrayAccess;

class User implements ArrayAccess
{
	// Data for this user
	protected $_data = array();

	public function __construct(array $data = null)
	{
		$roles = Storage\Factory::factory('Roles');

		// Set some defaults to begin with
		$this->_data = array(
			'id' => 0,
			'ip' => Application::get('input')->server->getRaw('REMOTE_ADDR'),
			'display_name' => 'Guest', // @todo lang string
			'language' => Settings::DEFAULT_LANG,
			'theme' => (int) Settings::DEFAULT_THEME,
			'user_token' => false, // @todo
			'roles' => array(
				'primary' => $roles->getRoleById($roles::ROLE_GUEST),
				'additional' => array(),
			),
		);

		if (null !== $data)
		{
			$this->setData($data);
		}
	}

	public function setData(array $data)
	{
		if (!empty($data['id_user']) && empty($this->_data['id_user']))
		{
			if (!ctype_digit($data['id_user']) || 0 > (int) $data['id_user'])
			{
				throw new Exception('User ID must be a positive integer.');
			}

			$this->_data['id'] = (int) $data['id_user'];
		}

		if (!empty($data['user_primary_role']))
		{
			$roles = Storage\Factory::factory('Roles');

			$this->_data['roles']['primary'] = $roles->getRoleById($data['user_primary_role']);
		}

		if (!empty($data['user_additional_roles']))
		{
			$roles = Storage\Factory::factory('Roles');

			// @todo should this be in a separate table? I think so.
			$temp = explode(',', $data['user_additional_roles']);

			foreach ($temp as $role)
			{
				$this->_data['roles']['additional'][] = $roles->getRoleById($role);
			}
		}

		if (!empty($data['user_theme']))
		{
			$this->_data['theme'] = (int) $data['user_theme'];
		}

		if (!empty($data['user_language']))
		{
			$this->_data['language'] = $data['user_language'];
		}

		if (!empty($data['user_login']))
		{
			$this->_data['login'] = $data['user_login'];
		}

		if (!empty($data['user_display_name']))
		{
			$this->_data['display_name'] = $data['user_display_name'];
		}

		if (!empty($data['user_email']))
		{
			$this->_data['email'] = $data['user_email'];
		}

		$event = new Event($this, 'org.smcore.user_data_set', array(
			'data' => $data,
		));

		Application::get('events')->fire($event);

		return $this;
	}

	public function setPassword($password)
	{
		$bcrypt = new Bcrypt();
		$encrypted = $bcrypt->encrypt($password);

		// @todo

		return $this;
	}

	/**
	 * Save this user's information to the database.
	 *
	 * @return boolean
	 */
	public function save()
	{
		return Storage\Factory::factory('Users')->save($this);
	}

	/**
	 * Check to see if the user has a certain permission.
	 *
	 * @param string $name The full name of the permission to check (i.e. 'org.simplemachines.forum.can_edit_posts')
	 *
	 * @return boolean
	 */
	public function hasPermission($name)
	{
		// Try the primary role first
		if (null !== $result = $this->_data['roles']['primary']->hasPermission($name))
		{
			return $result;
		}

		if (!empty($this->_data['roles']['additional']))
		{
			foreach ($this->_data['roles']['additional'] as $role)
			{
				if (null !== $result = $role->hasPermission($name))
				{
					return $result;
				}
			}
		}

		return false;
	}

	// ArrayAccess methods allow access via array indexes, such as $user['id']

	public function offsetExists($offset)
	{
		return isset($this->_data[$offset]);
	}

	public function offsetGet($offset)
	{
		if (array_key_exists($offset, $this->_data))
		{
			return $this->_data[$offset];
		}

		return false;
	}

	public function offsetSet($offset, $value)
	{
		$this->_data[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}

}