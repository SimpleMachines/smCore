<?php

namespace smCore;

class Settings
{
	const PATH = '/home/my_site/public_html';
	const MODULE_DIR = '/home/my_site/public_html/modules';
	const THEME_DIR = '/home/my_site/public_html/themes';
	const LANGUAGE_DIR = '/home/my_site/public_html/languages';
	const CACHE_DIR = '/home/my_site/public_html/cache';

	const URL = 'http://www.youdidntchangeyoursettingsfile.lol';

	const COOKIE_PATH = '/';
	const COOKIE_NAME = 'smcore_login';
	const COOKIE_DOMAIN = '.mysite.com';

	const TIMEZONE = 'America/Los_Angeles'; 
	const DEFAULT_LANG = 'en_US'; 
	const DEFAULT_THEME = 1;

	const MAIL_FROM = '';
	const MAIL_FROM_NAME = '';

	const UNIQUE_8 = '07h8fAN4';

	public static $database = array(
		'driver' => 'PDOMySql',
		'host' => 'localhost',
		'user' => '',
		'password' => '',
		'dbname' => '',
		'prefix' => 'smcore_',
	);
}