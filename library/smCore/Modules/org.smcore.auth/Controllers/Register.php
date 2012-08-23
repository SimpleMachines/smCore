<?php

/**
 * smCore Authentication Module - Register Controller
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

use smCore\Module\Controller, smCore\Storage;

class Register extends Controller
{
	public function preDispatch()
	{
		$this->module->loadLangPackage();
	}

	public function register()
	{
		// We're going to skip the agreement part for now. That might be SMF-only, or a plugin.
		return $this->module->render('register/start');

		$input = $this->_app['input'];

		if ($input->post->keyExists('register_agree'))
		{
			return $this->module->render('register/start');
		}
		else if ($input->post->keyExists('register_agree_young'))
		{
			return $this->module->render('register/start');
		}
		else
		{
			return $this->module->render('register/agreement');
		}
	}

	public function finish()
	{
		$input = $this->_app['input'];

		// Don't try to skip steps on us!
		if (!$input->post->keyExists('register_username'))
		{
			$this->_app['response']->redirect('/register/');
		}

		$username = $input->post->getRaw('register_username');
		$email = $input->post->getRaw('register_email');
		$pass1 = $input->post->getRaw('register_password');
		$pass2 = $input->post->getRaw('register_password2');

		// Spaces and other odd characters are evil...
		$username = trim(preg_replace('/[\t\n\r\x0B\0\x{A0}]+/u', ' ', $username));

		if (mb_strlen($username) < 1)
		{
			$this->module->throwLangException('register.username_too_short');
		}

		if (mb_strlen($username) > 60)
		{
			$this->module->throwLangException('register.username_too_long');
		}

		// Only these characters are permitted.
		if ('_' === $username || '|' === $username || 0 !== preg_match('/[<>&"\'=\\\\]/', preg_replace('/&#(?:\\d{1,7}|x[0-9a-fA-F]{1,6});/', '', $username)))
		{
			$this->module->throwLangException('register.username_invalid_characters');
		}

		if (mb_strtolower($username) === 'guest' || false !== strpos(mb_strtolower($username), 'admin'))
		{
			$this->module->throwLangException('register.username_reserved');
		}

		if ($pass1 != $pass2)
		{
			$this->module->throwLangExcption('register.password_mismatch');
		}

		if (empty($pass1))
		{
			$this->module->throwLangException('register.no_email');
		}

		// @todo Better email validation, not using a regex.
		if (empty($email) || 0 === preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $email) || mb_strlen($email) > 255)
		{
			$this->module->throwLangException('register.invalid_email');
		}

		$storage = Storage\Factory::factory('Users');

		// Check to see if this username is taken
		if (false !== $storage->getUserByName($username))
		{
			$this->module->throwLangException('register.username_taken');
		}

		// @todo add a findUserByData-ish method to storage, this query shouldn't be here
		$db = $this->_app['db'];

		if ($db->query("SELECT * FROM {db_prefix}users WHERE LOWER(user_email) = {string:email}", array('email' => mb_strtolower($email)))->rowCount() > 0)
		{
			$this->module->throwLangException('register.email_already_used');
		}

		$user = $storage
			->getUserById(0)
			->setData(array(
				'user_login' => $username,
				'user_display_name' => $username,
				'user_email' => $email,
			))
			->setPassword($pass1)
			->save()
		;

		return $this->module->render('register/finish');
	}

	public function activate()
	{
	}
}