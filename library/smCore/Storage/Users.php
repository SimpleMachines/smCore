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

use smCore\Application, smCore\Event, smCore\Exception, smCore\Model\User, smCore\Security\Session, smCore\Settings;

class Users
{
	protected $_current_user;
	protected $_loaded_users = array();

	public function getCurrentUser()
	{
		if (null === $this->_current_user)
		{
			if (Session::exists())
			{
				Session::start();

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
		$db = Application::get('db');

		$result = $db->query("
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
		$user = new User($row);
		$user->setData($row);

		return $user;
	}

	public function getUserByEmail($email)
	{
		$db = Application::get('db');

		$result = $db->query("
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
		$user = new User($row);
		$user->setData($row);

		return $user;
	}

	public function getUserById($id)
	{
		// The User class will check if this is a good ID.
		$user = new User(array(
			'id_user' => $id
		));

		if ($id < 1)
		{
			return $user;
		}

		$cache = Application::get('cache');

		// If we've already fetched the data, there's no reason to grab it again
		if (null === $data = $cache->load('user_data_' . $id))
		{
			$db = Application::get('db');

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

		$this->setData($data);

		return $user;
	}

	public function save(User $user)
	{
		$db = Application::get('db');

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

		$event = new Event($this, 'org.smcore.user_data_save', array(
			'user' => $user,
		));

		Application::get('events')->fire($event);

	}
}