<?php

/**
 * smCore Admin Module - Maintenance Controller
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

class Maintenance extends Controller
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

		$tasks = array(
			'clear_cache' => array(
				'name' => $module->lang('maintenance.empty_cache'),
				'help' => $module->lang('maintenance.empty_cache.help'),
				'link' => '#',
				'button_text' => $module->lang('maintenance.empty_cache.button'),
			),
			'empty_logs' => array(
				'name' => $module->lang('maintenance.empty_logs'),
				'help' => $module->lang('maintenance.empty_logs.help'),
				'link' => '#',
				'button_text' => $module->lang('maintenance.empty_logs.button'),
			),
		);

		return $this->_getParentModule()->render('maintenance/main', array(
			'tasks' => $tasks
		));
	}

	public function cache()
	{
			$cache_info = Application::get('cache')->getStats();
	
			return $this->_getParentModule()->render('maintenance/cache', array(
				'cache_stats' => $cache_info,
			));
	}
}