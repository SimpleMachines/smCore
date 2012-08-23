<?php

/**
 * smCore Authentication Module - Log In/Out Controller
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

namespace smCore\Modules\Auth\Controllers;

use smCore\Module\Controller, smCore\Security\Crypt\Bcrypt, smCore\Security\Session, smCore\Storage;

class LogInOut extends Controller
{
	public function preDispatch()
	{
		$this->module->loadLangPackage();
	}

	public function login()
	{
		$input = $this->_app['input'];

		// I'd actually like to use the router to route to a different method depending on whether this was a GET or a POST
		if ($input->post->keyExists('submit'))
		{
			$username = $input->post->getRaw('login_user');
			$password = $input->post->getRaw('login_pass');

			$storage = Storage\Factory::factory('Users');

			if (false === $user = $storage->getUserByName($username))
			{
				if (false !== strpos($username, '@', 1))
				{
					$user = $storage->getUserByEmail($username);
				}

				if (false === $user)
				{
					return $this->module->render('login', array(
						'failed' => true,
						'username' => $username,
					));
				}
			}

			$bcrypt = new Bcrypt();

			if ($bcrypt->match($password, $user['password']))
			{
				if ($input->post->keyExists('login_forever'))
				{
					// Six years of seconds!
					$this->_app['session']->setLifetime(189216000);
				}
				else
				{
					$minutes = $input->post->getInt('login_length');

					// Minimum login time is 15 minutes
					if ($minutes < 15)
					{
						$minutes = 60;
					}

					$this->_app['session']->setLifetime($minutes * 60);
				}

				$this->_app['session']->start();
				$_SESSION['id_user'] = $user['id'];

				// @todo: $module->fire('post_successful_login');

				if (!empty($_SESSION['redirect_url']))
				{
					$url = $_SESSION['redirect_url'];
				}
				else
				{
					$url = null;
				}

				$this->_app['response']->redirect($url);
			}

			setcookie($this->_app['settings']['cookie_name'], '', 0, $this->_app['settings']['url'], $this->_app['settings']['cookie_domain']);

			return $this->module->render('login', array(
				'failed' => true,
				'username' => $username,
			));
		}

		return $this->module->render('login', array(
			'username' => '',
		));
	}

	public function logout()
	{
		$this->_app['session']->end();
		$this->_app['response']->redirect();
	}
}