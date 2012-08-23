<?php

/**
 * smCore Admin Module - Main Controller
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

namespace smCore\Modules\Admin\Controllers;

use smCore\Module\Controller;

class Main extends Controller
{
	public function preDispatch($method)
	{
		$this->module
			->requireAdmin()
			->loadLangPackage();

		if ($method !== 'authenticate')
		{
			$this->module->validateSession('admin');
		}
	}

	public function main()
	{
		$app = $this->_app;

		return $this->module->render('main', array(
			'smcore_version' => '???', // @todo: fetch from smCore.org
			'installed_smcore_version' => $app::VERSION,
			'end_session_token' => $this->module->createToken('end_admin_session'),
		));
	}

	public function authenticate()
	{
		if ($this->_app['input']->post->keyExists('authenticate_pass'))
		{
			$this->module->validateSession('admin');
		}

		return $this->module->render('admin_login');
	}

	public function endSession()
	{
		if (false !== $token = $this->_app['input']->get->getAlnum('token'))
		{
			$this->module->checkToken('end_admin_session', $token);

			$this->module->endSession('admin');

			// Go back to the home page, since you're obviously not
			$this->_app['response']->redirect();
		}

		$this->module->throwLangException('end_session.missing_token');
	}
}