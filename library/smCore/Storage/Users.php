<?php

/**
 * smCore Users Storage
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

namespace smCore\Storage;

use smCore\Event, smCore\Exception, smCore\Model\User, smCore\Security\Session;

class Users extends AbstractStorage
{
	protected $_current_user;
	protected $_loaded_users = array();

	public function getCurrentUser()
	{
		if (null === $this->_current_user)
		{
			if ($this->_app['session']->exists())
			{
				$this->_app['session']->start();

				if (isset($_SESSION['id_user']))
				{
					// The identity just holds an ID right now...
					$this->_current_user = $this->getUserById(intval($_SESSION['id_user']));
				}
			}

			if (null === $this->_current_user)
			{
				$this->_current_user = $this->getUserById(0);
			}
		}

		return $this->_current_user;
	}

	public function getUserByName($name)
	{
		$result = $this->_app['db']->query("
			SELECT *
			FROM {db_prefix}users
			WHERE LOWER(user_login) = {string:name}
				OR LOWER(user_display_name) = {string:name}",
			array(
				'name' => $name,
			)
		);

		if ($result->rowCount() < 1)
		{
			return false;
		}

		$row = $result->fetch();
		$user = new User($this->_app, $row);
		$user->setData($row);

		return $user;
	}

	public function getUserByEmail($email)
	{
		$result = $this->_app['db']->query("
			SELECT *
			FROM {db_prefix}users
			WHERE LOWER(user_email) = {string:email}",
			array(
				'email' => $email,
			)
		);

		if ($result->rowCount() < 1)
		{
			return false;
		}

		$row = $result->fetch();
		$user = new User($this->_app, $row);
		$user->setData($row);

		return $user;
	}

	public function getUserById($id)
	{
		// The User class will check if this is a good ID.
		$user = new User($this->_app, array(
			'id_user' => $id,
		));

		if ($id < 1)
		{
			return $user;
		}

		$cache = $this->_app['cache'];

		// If we've already fetched the data, there's no reason to grab it again
		if (false === $data = $cache->load('user_data_' . $id))
		{
			$db = $this->_app['db'];

			$result = $db->query("
				SELECT *
				FROM {db_prefix}users
				WHERE id_user = {int:id}",
				array(
					'id' => $id,
				)
			);

			if ($result->rowCount() < 1)
			{
				throw new Exception('Tried to load a user with an invalid ID.');
			}

			$data = $result->fetch();

			$cache->save($data, 'user_data_' . $id);
		}

		$user->setData($data);

		return $user;
	}

	public function save(User $user)
	{
		$db = $this->_app['db'];

		if ($user['id'] < 1)
		{
			$id = $db->insert('users', array(
				'user_login' => $user['login'],
				'user_display_name' => $user['display_name'],
				'user_email' => $user['email'],
				'user_pass' => $user['password'],
				'user_primary_role' => $user['roles']['primary']->getId(),
				'user_additional_roles' => '',
				'user_registered' => time(),
				'user_language' => $user['language'],
				'user_theme' => $user['theme'],
			));
		}
		else
		{
			
		}

		$this->_app['events']->fire('org.smcore.user_data_save', array(
			'user' => $user,
		));

	}
}