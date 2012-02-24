<?php

namespace smCore\Modules\Users\Models;
use smCore\Application, smCore\Settings, smCore\Request, smCore\Module, smCore\Security\Session;

class Main extends Module\Model
{
	protected $_authAdapter = null;

	/**
	 * Authenticate the user's login details, then load them up.
	 *
	 * @access public
	 */
	public function processLogin()
	{
		$module = $this->_getParentModule();
		$input = Application::get('input');

		$username = strtolower($input->post->getRaw('username'));
		$password_hashed = $input->post->getRaw('password_hashed');
		$length = min(189216000, max(1800, $input->post->getInt('cookie_time')));

		if (!$username)
			$module->throwLangException('exceptions.login.no_username_sent');

		// Did they not have JavaScript enabled?
		if (!$password_hashed)
		{
			$password = $input->post->getRaw('password');

			// No plaintext password, either
			if (!$password)
				$module->throwLangException('exceptions.login.no_password_sent');

			$password_hashed = hash('sha256', $password);
		}

		$db = Application::get('db');

		$passSelect = new \Zend_Db_Expr($db->quoteInto('SHA1(CONCAT(?, user_salt)) AS match_hashed', $password_hashed));

		$query = $db->select()
			->from('beta_users', array('*', $passSelect))
			->where('user_login = ?', $username);

		$result = $db->query($query);

		if ($result->rowCount() < 1)
			$module->throwLangException('exceptions.login.identity_not_found');

		$user_data = $result->fetch();

		if ($user_data->user_active < 1)
			$module->throwLangException('exceptions.login.account_not_active');

		if ($user_data->user_pass != $user_data->match_hashed)
			$module->throwLangException('exceptions.login.credential_invalid');

		// @todo: start session!
		Session::setLifetime($length);

		Session::start();
		$_SESSION['id_user'] = $user_data->id_user;
	}

	/**
	 * Clear the stored identity and forget the session
	 *
	 * @access public
	 */
	public function logout()
	{
		Session::end();
	}
}