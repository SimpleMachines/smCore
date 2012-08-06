<?php
/*
 * 
 */

namespace smCore;

class Settings
{
	// please... no trailing slashes
	const PATH = '/home/my_site/public_html';
	const MODULE_DIR = '/home/my_site/public_html/modules';
	const THEME_DIR = '/home/my_site/public_html/themes';
	// this is used both for the file cache (if enabled) and the TWIG template cache
	const CACHE_DIR = '/home/my_site/public_html/cache';

	// again, no slashes at the end - www.example.com/smcore *not* www.example.com/smcore/
	const URL = 'http://www.youdidntchangeyoursettingsfile.lol';
	
	// you'll probably only need to configure COOKIE_DOMAIN
	const COOKIE_PATH = '/';
	// change this if running multiple installs
	const COOKIE_NAME = 'smcore_login';
	const COOKIE_DOMAIN = '.mysite.com';

	const TIMEZONE = 'America/Los_Angeles'; 
	const DEFAULT_LANG = 'en_US'; 
	const DEFAULT_THEME = 1;

	const MAIL_FROM = '';
	const MAIL_FROM_NAME = '';

	// this string is used so SMF cache's don't interfere with one another
	const UNIQUE_8 = '07h8fAN4';
	
	public static $database = array(
		// you have to use PDOMySql at the moment
		'driver' => 'PDOMySql',
		// localhost will be right in 99% of cases
		'host' => 'localhost',
		// database username, often 'root' on local test machines
		'user' => '',
		'password' => '',
		'dbname' => '',
		// all smCore database tables will be prefixed with the following
		'prefix' => 'smcore_',
	);
	
	public static $cache = array(
		'adapter' => 'file',
		// other options can be added here dependant upon the adapter type
		/*
		 * these are the key options
		file
			string dir
		memcached
			array_string servers
			bool persistent
			int connect_timeout
			int retry_timeout
		*/
		'dir' => Settings::CACHE_DIR,
	);
}