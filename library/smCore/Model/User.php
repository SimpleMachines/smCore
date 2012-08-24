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

use smCore\Application, smCore\Event, smCore\Exception, smCore\Security\Crypt\Bcrypt, smCore\Storage;
use ArrayAccess;

class User extends AbstractModel implements ArrayAccess
{
	// Data for this user
	protected $_data = array();

	/**
	 * Create a new User object
	 *
	 * @param smCore\Application $app
	 * @param array              $data
	 */
	public function __construct(Application $app, array $data = null)
	{
		parent::__construct($app);

		$roles = $this->_app['storage_factory']->factory('Roles');

		// Set some defaults to begin with
		$this->_data = array(
			'id' => 0,
			'ip' => $this->_app['input']->server->getRaw('REMOTE_ADDR'),
			'display_name' => 'Guest', // @todo lang string
			'language' => $this->_app['settings']['default_lang'],
			'theme' => (int) $this->_app['settings']['default_theme'],
			'token' => '', // @todo
			'email' => '',
			'roles' => array(
				'primary' => $roles->getRoleById(Storage\Roles::ROLE_GUEST),
				'additional' => array(),
			),
			'password' => null,
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
			if (!ctype_digit((string) $data['id_user']) || 0 > (int) $data['id_user'])
			{
				throw new Exception('User ID must be a positive integer.');
			}

			$this->_data['id'] = (int) $data['id_user'];
		}

		$roles = $this->_app['storage_factory']->factory('Roles');

		if (!empty($data['user_primary_role']))
		{
			$this->_data['roles']['primary'] = $roles->getRoleById($data['user_primary_role']);
		}

		if (!empty($data['user_additional_roles']))
		{
			// @todo should this be in a separate table? I think so.
			$temp = explode(',', $data['user_additional_roles']);

			foreach ($temp as $role)
			{
				$this->_data['roles']['additional'][] = $roles->getRoleById($role);
			}
		}

		if (!empty($data['user_theme']) && ctype_digit($data['user_theme']))
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

		if (!empty($data['user_pass']))
		{
			$this->_data['password'] = $data['user_pass'];
		}

		$this->_app['events']->fire('org.smcore.user_data_set', array(
			'user' => $this,
			'data' => &$data,
		));

		return $this;
	}

	public function setRawData(array $data)
	{
		foreach ($data as $key => $value)
		{
			if ('password' === $key)
			{
				throw new Exception('User passwords must be set via the setPassword method.');
			}

			$this->_data[$key] = $value;
		}
	}

	/**
	 * Set a user's password
	 *
	 * @param string $password 
	 *
	 * @return 
	 */
	public function setPassword($password)
	{
		$bcrypt = new Bcrypt();

		$this->_data['password'] = $bcrypt->encrypt($password);

		return $this;
	}

	/**
	 * Save this user's information to the database.
	 *
	 * @return boolean
	 */
	public function save()
	{
		return $this->_app['storage_factory']->factory('Users')->save($this);
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

	public function hasRole($role)
	{
		if ($role instanceof Role)
		{
			$role = $role->getId();
		}
		else if (!is_int($role))
		{
			throw new Exception('hasRole() expected a Role or integer role ID.');
		}

		if ($this->_data['roles']['primary']->getId() === $role)
		{
			return true;
		}

		foreach ($this->_data['roles']['additional'] as $additional)
		{
			if ($additional->getId() === $role)
			{
				return true;
			}
		}

		return false;
	}

	public function isAdmin()
	{
		return $this->hasRole(Storage\Roles::ROLE_ADMIN);
	}

	public function isLoggedIn()
	{
		return $this->hasRole(Storage\Roles::ROLE_MEMBER);
	}

	/**
	 * ArrayAccess - implementation for empty/isset/array_key_exists/etc.
	 *
	 * @param mixed $offset
	 *
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->_data[$offset]);
	}

	/**
	 * ArrayAccess - implementation for getting data via array syntax
	 *
	 * @param mixed $offset Name of the key, usually a string
	 *
	 * @return boolean
	 */
	public function offsetGet($offset)
	{
		if (array_key_exists($offset, $this->_data))
		{
			return $this->_data[$offset];
		}

		return false;
	}

	/**
	 * ArrayAccess - implementation for setting data via array syntax
	 *
	 * @param mixed $offset Name of the key, usually a string
	 * @param mixed $value  
	 */
	public function offsetSet($offset, $value)
	{
		if ('password' === $offset)
		{
			throw new Exception('User passwords cannot be set via array access.');
		}

		$this->_data[$offset] = $value;
	}

	/**
	 * ArrayAccess - implementation for unsetting data via array syntax
	 *
	 * @param mixed $offset Name of the key, usually a string
	 */
	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}

	public function __toString()
	{
		return $this->_data['display_name'] . ' (' . $this->_data['roles']['primary']->getName() . ')';
	}
}