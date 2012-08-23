<?php

/**
 * smCore Admin Module - Configuration Controller
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

use smCore\Module\Controller, smCore\Form, smCore\Form\Control;

class Configuration extends Controller
{
	public function preDispatch($method)
	{
		$this->module
			->requireAdmin()
			->loadLangPackage();
	}

	public function main()
	{
		$settings = $this->_app['settings'];

		$form = new Form($settings['url'] . '/admin/settings/');

		$form->addControl('urls_directories', new Control\Group(array(
			'label' => $this->module->lang('settings.urls_directories'),
			'controls' => array(
				'url' => new Control\Text(array(
					'label' => $this->module->lang('settings.url'),
					'value' => $settings['url'],
					'help' => $this->module->lang('settings.url.help'),
				)),
				'module_dir' => new Control\Text(array(
					'label' => $this->module->lang('settings.module_dir'),
					'value' => $settings['module_dir'],
					'help' => $this->module->lang('settings.module_dir.help'),
				)),
				'theme_dir' => new Control\Text(array(
					'label' => $this->module->lang('settings.theme_dir'),
					'value' => $settings['theme_dir'],
					'help' => $this->module->lang('settings.theme_dir.help'),
				)),
				'cache_dir' => new Control\Text(array(
					'label' => $this->module->lang('settings.cache_dir'),
					'value' => $settings['cache_dir'],
					'help' => $this->module->lang('settings.cache_dir.help'),
				)),
			),
		)));

		$form->addControl('caching', new Control\Group(array(
			'label' => $this->module->lang('settings.caching'),
			'help' => 'These settings control blah blah blah.',
			'controls' => array(
				'cache_driver' => new Control\Select(array(
					'label' => $this->module->lang('settings.cache_driver'),
					'value' => $settings['cache']['driver'],
					'help' => $this->module->lang('settings.cache_driver.help'),
					'options' => array(
						'File' => $this->module->lang('settings.cache_driver_file'),
						'Memcached' => $this->module->lang('settings.cache_driver_memcached'),
						'APC' => $this->module->lang('settings.cache_driver_apc'),
						'Blackhole' => $this->module->lang('settings.cache_driver_blackhole'),
					),
				)),
				'cache_default_ttl' => new Control\Text(array(
					'label' => $this->module->lang('settings.cache_default_ttl'),
					'value' => $settings['cache']['default_ttl'],
					'help' => $this->module->lang('settings.cache_default_ttl.help'),
				)),
			),
		)));

		$form->addControl('cookies_sessions', new Control\Group(array(
			'label' => $this->module->lang('settings.cookies_sessions'),
			'controls' => array(
				'cookie_name' => new Control\Text(array(
					'label' => $this->module->lang('settings.cookie_name'),
					'value' => $settings['cookie_name'],
				)),
				'cookie_domain' => new Control\Text(array(
					'label' => $this->module->lang('settings.cookie_domain'),
					'value' => $settings['cookie_domain'],
				)),
				'cookie_path' => new Control\Text(array(
					'label' => $this->module->lang('settings.cookie_path'),
					'value' => $settings['cookie_path'],
				)),
				'sessions_db_driven' => new Control\Checkbox(array(
					'label' => $this->module->lang('settings.session_db_driven'),
					'value' => $settings['session_db_driven'],
					'help' => $this->module->lang('settings.session_db_driven.help'),
				)),
			),
		)));

		return $this->module->render('configuration/form', array(
			'form' => $form,
		));
	}
}