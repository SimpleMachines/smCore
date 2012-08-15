<?php

/**
 * smCore Admin Module - Modules Controller
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

use smCore\Application, smCore\Module\Controller, smCore\Settings;

class Modules extends Controller
{
	public function preDispatch()
	{
		$this->_getParentModule()
			->loadLangPackage()
			->requireAdmin()
			->validateSession('admin')
		;
	}

	public function main()
	{
		$module = $this->_getParentModule();
		$modules_storage = Application::get('modules')->getLoadedModules();

		$modules = array();

		foreach ($modules_storage as $identifier => $module_object)
		{
			$config = $module_object->getConfig();

			$modules[$identifier] = array(
				'name' => $config['name'],
				'version' => $config['version'],
				'admin_route' => false,
			);
		}

		return $this->_getParentModule()->render('modules/main', array(
			'modules' => $modules,
		));
	}
}