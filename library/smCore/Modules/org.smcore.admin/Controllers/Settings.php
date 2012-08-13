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

use smCore\Application, smCore\Module\Controller, smCore\Settings as smcSettings;

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

		return $module->render('settings/main', array(
			'settings' => array(
				'url' => smcSettings::URL,
				'modules_dir' => smcSettings::MODULE_DIR,
				'themes_dir' => smcSettings::THEME_DIR,
				'cache_dir' => smcSettings::CACHE_DIR,
				'cookie_dir' => smcSettings::COOKIE_PATH,
				'cookie_name' => smcSettings::COOKIE_NAME,
				'cookie_domain' => smcSettings::COOKIE_DOMAIN,
				'cookie_db_driven' => (bool) smcSettings::SESSION_DB_DRIVEN,
			),
		));
	}
}