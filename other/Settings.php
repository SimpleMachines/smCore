<?php

/**
 * smCore platform
 *
 * @package smCore
 * @license MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
*/

class Settings
{
	const APP_PATH = '/smcore';
	const APP_MODULE_DIR = '/smcore/modules';
	const APP_THEME_DIR = '/smcore/themes';
	const APP_LANGUAGE_DIR = '/smcore/languages';
	const APP_CACHE_DIR = '/smcore/cache';

	const APP_URL = 'http://myserver.com';

	const COOKIE_PATH = '/';
	const COOKIE_NAME = 'funz'; // wha'?
	const COOKIE_DOMAIN = '.myserver.com';

	const APP_TIMEZONE = 'America/Los_Angeles';
	const APP_DEFAULT_LANG = 'english_us';
	const APP_DEFAULT_EMAIL = 'noreply@myserver.com';

	public static $database = array(
		'adapter' => 'Mysqli',
		'host' => 'localhost',
		'username' => '',
		'password' => '',
		'dbname' => '',
		'profiler' => true,
		'db_show_debug' => true,
	);
}