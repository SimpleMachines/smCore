<?php

namespace smCore\Modules\Users\Models;
use smCore\Application, smCore\Settings, smCore\Request, smCore\Exception, smCore\Module, smCore\Utility,
	Zend_Db_Expr, Zend_Mail;

class Register extends Module\Model
{
	function processRegistration()
	{
		$module = $this->_getParentModule();
		$db = Application::get('db');

		$username = trim(Application::get('input')->post->noTags('new_account_username') ?: '');
		$login = mb_strtolower($username);
		$email = mb_strtolower(trim(Application::get('input')->post->testEmail('new_account_email') ?: ''));
		$password_hashed = Application::get('input')->post->getRaw('password_hashed') ?: '';

		if (empty($username))
			$module->throwLangException('exceptions.register.username_required');

		if (empty($email))
			$module->throwLangException('users.exceptions.register.invalid_email');

		// No hashed password? Try to create one.
		if (!preg_match('~^[a-f0-9]{64}$~', $password_hashed))
		{
			$password = Application::get('input')->post->getRaw('new_account_password');

			// No plaintext password, either
			if (!$password)
				$module->throwLangException('users.exceptions.register.no_password_sent');

			$password_hashed = hash('sha256', $password);
		}

		$result = $db->query("
			SELECT *
			FROM beta_users
			WHERE user_login = ?
				OR user_email = ?
			LIMIT 1",
			array(
				$login,
				$email,
			)
		);

		if ($result->rowCount() > 0)
		{
			$found = $result->fetch();

			if ($found->user_email == $email)
				$module->throwLangException('users.exceptions.register.email_already_exists');

			$module->throwLangException('users.exceptions.register.username_already_exists');
		}

		// @todo: add user
		$salt = Utility::randString(32, 'hex');
		$activation = Utility::randString(25, 'hex');
		$token = md5($password_hashed . time());

		$result = $db->insert('beta_users', array(
			'user_given_name' => $username,
			'user_display_name' => $username,
			'user_login' => $login,
			'user_email' => $email,
			'user_registered' => Application::get('time'),
			'user_token' => $token,
			'user_active' => 0,
			'user_pass' => new Zend_Db_Expr('SHA1(\'' . $password_hashed . $salt . '\')'),
			'user_salt' => $salt,
			'user_primary_role' => 2,
			'user_additional_roles' => '',
		));

		if (!$result)
			$module->throwLangException('users.exceptions.register.unknown_error');

		$id = $db->lastInsertId();

		return array(
			'id' => $id,
			'username' => $username,
			'email' => $email,
		);
	}

	function sendActivationEmail($user_id = 0)
	{
		$module = $this->_getParentModule();
		$db = Application::get('db');

		$user_id = (int) $user_id;

		$new_activation = Utility::randString(32, 'hex');

		// Set the new activation string in the database
		$result = $db->update('beta_users', array('user_activation' => $new_activation), 'id_user = ' . $user_id);

		if (!$result)
			$module->throwLangException('users.exceptions.register.invalid_id');

		$result = $db->query("
			SELECT user_display_name, user_email
			FROM beta_users
			WHERE id_user = ?",
			array(
				$user_id,
			)
		);

		$user_data = $result->fetch();

		$replacements = array(
			Settings::URL,
			$new_activation,
			$user_id,
		);
Application::get('mail');
		$mail = new Zend_Mail();
		$mail->setBodyText($module->lang('users.register.email_activate_account.text', $replacements));
		$mail->setBodyHtml($module->lang('users.register.email_activate_account.html', $replacements));
		$mail->addTo($user_data->user_email, $user_data->user_display_name);
		$mail->setSubject($module->lang('users.register.email_activate_account.subject'));
		$mail->send();
	}

	function activateAccount()
	{
		$module = $this->_getParentModule();
		$db = Application::get('db');

		$id = Application::get('input')->get->getInt('id');
		$key = Application::get('input')->get->getAlnum('key');

		if (!$id || !$key || strlen($key) != 32)
			$module->throwLangException('exceptions.register.activate_invalid_link');

		$result = $db->query("
			SELECT *
			FROM beta_users
			WHERE id_user = ?",
			array(
				$id,
			)
		);

		if ($result->rowCount() < 1)
			$module->throwLangException('users.exceptions.register.activate_id_doesnt_exist');

		$user_data = $result->fetch();

		if ($user_data->user_active > 0)
			$module->throwLangException('users.exceptions.register.already_activated');

		if ($user_data->user_activation !== $key)
			$module->throwLangException('users.exceptions.register.wrong_key');

		// Everything seems to be in order
		return $db->update('beta_users', array(
			'user_active' => 1,
			'user_activation' => '',
		), 'id_user = ' . $id);
	}
}