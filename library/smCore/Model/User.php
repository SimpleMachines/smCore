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
use smCore\Application, smCore\Settings, smCore\Storage\Factory as StorageFactory, ArrayAccess;

class User implements ArrayAccess
{
	// Data for this user
	protected $_primary_role;
	protected $_additional_roles;
	protected $_data = array();

	public function __construct($id)
	{
		if (!is_int($id))
			throw new Exception('Invalid user ID.');

		$roles = StorageFactory::getStorage('Roles');

		if ($id > 0)
		{
			$cache = Application::get('cache');

			//if (($data = $cache->load('core_user_' . $id)) === null)
			{
				$db = Application::get('db');

				$result = $db->query("
					SELECT *
					FROM beta_users
					WHERE id_user = ?",
					array(
						$id,
					)
				);

				if ($result->rowCount() < 1)
					throw new Exception('Tried to load a user with an invalid ID.');

				$data = $result->fetch();

				$cache->save($data, 'core_user_' . $id);
			}

			$this->_primary_role = $roles->getRoleById($data->user_primary_role);

			$this->_data = (array) $data;

			// Some shortcuts. We should just make a new array, probably.
			$this->_data['id'] = $data->id_user;
			$this->_data['theme'] = (int) $data->user_theme;
			$this->_data['language'] = (int) $data->user_language;
			$this->_data['display_name'] = $data->user_display_name;

			if (!empty($data->user_additional_roles))
			{
			}
		}
		else
		{
			$this->_data = array(
				'id' => 0,
				'display_name' => 'Guest',
				'language' => (int) Settings::DEFAULT_LANG, // @todo: lang
				'theme' => (int) Settings::DEFAULT_THEME,
			);

			$this->_primary_role = $roles->getRoleById($roles::ROLE_GUEST);
		}
	}

	public function hasPermission($name)
	{
		// Try the primary role first
		$primary = $this->_primary_role->hasPermission($name);

		if ($primary !== null)
			return $primary;

		// @todo: Try additional roles too

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
			return $this->_data[$offset];

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