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

use smCore\Application, smCore\Settings, smCore\Model\User, smCore\Exception, smCore\Security\Session;

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

	public function getUserById($id)
	{
		if (!is_int($id))
		{
			throw new Exception('Invalid user ID');
		}

		// @todo: load user from database info
		return new User($id);
	}
}