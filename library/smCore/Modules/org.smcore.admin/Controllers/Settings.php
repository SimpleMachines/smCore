<?php

/**
 * smCore Admin Module - Settings Controller
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

use smCore\Application, smCore\Module\Controller, smCore\Form, smCore\Form\Control, smCore\Settings as smcSettings;

class Settings extends Controller
{
	public function preDispatch($method)
	{
		$this->_getParentModule()
			->requireAdmin()
			->loadLangPackage();
	}

	public function main()
	{
		$module = $this->_getParentModule();

		$form = new Form(smcSettings::URL . '/admin/settings/');

		$form->addControl('urls_directories', new Control\Group(array(
			'label' => $module->lang('settings.urls_directories'),
			'controls' => array(
				'url' => new Control\Text(array(
					'label' => $module->lang('settings.url'),
					'value' => smcSettings::URL,
					'help' => $module->lang('settings.url.help'),
				)),
				'module_dir' => new Control\Text(array(
					'label' => $module->lang('settings.module_dir'),
					'value' => smcSettings::MODULE_DIR,
					'help' => $module->lang('settings.module_dir.help'),
				)),
				'theme_dir' => new Control\Text(array(
					'label' => $module->lang('settings.theme_dir'),
					'value' => smcSettings::THEME_DIR,
					'help' => $module->lang('settings.theme_dir.help'),
				)),
				'cache_dir' => new Control\Text(array(
					'label' => $module->lang('settings.cache_dir'),
					'value' => smcSettings::CACHE_DIR,
					'help' => $module->lang('settings.cache_dir.help'),
				)),
			),
		)));

		$form->addControl('caching', new Control\Group(array(
			'label' => $module->lang('settings.caching'),
			'help' => 'These settings control blah blah blah.',
			'controls' => array(
				'cache_driver' => new Control\Select(array(
					'label' => $module->lang('settings.cache_driver'),
					'value' => smcSettings::$cache['driver'],
					'help' => $module->lang('settings.cache_driver.help'),
					'options' => array(
						'File' => $module->lang('settings.cache_driver_file'),
						'Memcached' => $module->lang('settings.cache_driver_memcached'),
						'APC' => $module->lang('settings.cache_driver_apc'),
						'Blackhole' => $module->lang('settings.cache_driver_blackhole'),
					),
				)),
				'cache_default_ttl' => new Control\Text(array(
					'label' => $module->lang('settings.cache_default_ttl'),
					'value' => smcSettings::$cache['default_ttl'],
					'help' => $module->lang('settings.cache_default_ttl.help'),
				)),
			),
		)));

		$form->addControl('cookies_sessions', new Control\Group(array(
			'label' => $module->lang('settings.cookies_sessions'),
			'controls' => array(
				'cookie_name' => new Control\Text(array(
					'label' => $module->lang('settings.cookie_name'),
					'value' => smcSettings::COOKIE_NAME,
				)),
				'cookie_domain' => new Control\Text(array(
					'label' => $module->lang('settings.cookie_domain'),
					'value' => smcSettings::COOKIE_DOMAIN,
				)),
				'cookie_path' => new Control\Text(array(
					'label' => $module->lang('settings.cookie_path'),
					'value' => smcSettings::COOKIE_PATH,
				)),
				'sessions_db_driven' => new Control\Checkbox(array(
					'label' => $module->lang('settings.cookie_db_driven'),
					'value' => (bool) smcSettings::SESSION_DB_DRIVEN,
					'help' => $module->lang('settings.cookie_db_driven.help'),
				)),
			),
		)));

		return $module->render('settings/form', array(
			'form' => $form,
		));
	}
}