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

use smCore\Module\Controller;

class Modules extends Controller
{
	public function preDispatch()
	{
		$this->module
			->loadLangPackage()
			->requireAdmin()
			->validateSession('admin')
		;
	}

	public function main()
	{
		$modules_storage = $this->_app['modules']->getLoadedModules();

		$modules = array();

		foreach ($modules_storage as $identifier => $module_object)
		{
			$config = $module_object->getConfig();
			$routes = $module_object->getRoutes();

			$modules[$identifier] = array(
				'name' => $config['name'],
				'version' => $config['version'],
				'author' => $config['author'],
				'admin_route' => false,
			);

			if (isset($routes['admin']))
			{
				if (is_array($routes['admin']['match']))
				{
				}
				else
				{
					$modules[$identifier]['admin_route'] = $routes['admin']['match'];
				}
			}
		}

		return $this->module->render('modules/main', array(
			'modules' => $modules,
		));
	}
}