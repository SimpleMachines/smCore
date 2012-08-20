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
		$module = $this->_getParentModule();

		return $module->render('main');
	}

	public function authenticate()
	{
		if ($this->app['input']->post->keyExists('authenticate_pass'))
		{
			$this->module->validateSession('admin');
		}

		return $this->module->render('admin_login');

		{
		}

	}
}