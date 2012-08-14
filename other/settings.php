<?php
/*
 * 
 */

namespace smCore;

class Settings
{
	/**
	 * These values should not include trailing slashes.
	 *
	 * PATH       Path where index.php is located
	 * MODULE_DIR Path to the /modules/ directory
	 * THEME_DIR  Path to the /themes/ directory
	 * CACHE_DIR  File cache directory, used by the Twig template cache and the File cache if enabled
	 */
	const PATH = '/home/my_site/public_html';
	const MODULE_DIR = '/home/my_site/public_html/modules';
	const THEME_DIR = '/home/my_site/public_html/themes';
	const CACHE_DIR = '/home/my_site/public_html/cache';

	/**
	 * Again, no slashes at the end - http://www.example.com/smcore *not* http://www.example.com/smcore/
	 */
	const URL = 'http://www.youdidntchangeyoursettingsfile.lol';

	/**
	 * You'll probably only need to configure COOKIE_DOMAIN
	 */
	const COOKIE_PATH = '/';
	const COOKIE_NAME = 'smcore_login';
	const COOKIE_DOMAIN = '.mysite.com';

	/**
	 * Use database-driven sessions instead of file-based sessions?
	 *
	 * @todo: Change this to a string, to allow for memcached/etc. to be used for sessions?
	 */
	const SESSION_DB_DRIVEN = true;

	/**
	 * @todo: Default time zone should be a database setting and only used to display times, not store them
	 */
	const TIMEZONE = 'America/Los_Angeles'; 
	const DEFAULT_LANG = 'en_US'; 
	const DEFAULT_THEME = 1;

	const MAIL_FROM = '';
	const MAIL_FROM_NAME = '';

	/**
	 * This string is used so multiple smCore installations don't interfere with each other
	 */
	const UNIQUE_8 = '07h8fAN4';

	/**
	 * Database connection parameters
	 *
	 * Available drivers: PDOMySql
	 *
	 * Options:
	 *     PDOMySQL:
	 *         host     string Hostname of the MySQL server, usually localhost
	 *         user     string Your MySQL username
	 *         password string Your MySQL password
	 *         dbname   string The name of the database to connect to
	 *     All:
	 *         prefix   string Prefix all table names with this string, to prevent clashes with other software or other smCore installations
	 */
	public static $database = array(
		'driver'   => 'PDOMySql',
		'host'     => 'localhost',
		'user'     => '',
		'password' => '',
		'dbname'   => '',
		'prefix'   => 'smcore_',
	);

	/**
	 * Caching mechanism parameters
	 *
	 * Available drivers: Memcached, Blackhole, APC, File
	 *
	 * Options:
	 *     APC:
	 *         (none)
	 *     Blackhole:
	 *         (none)
	 *     File:
	 *         directory       string  
	 *     Memcached:
	 *         servers         array   Array of servers, keys are "host", "port", "weight"
	 *         persistent      boolean Utilize a persistent connection to the Memcached server
	 *         connect_timeout int     Time to wait before considering a connection failed
	 *         retry_timeout   int     Time to wait before attempting to retry an operation
	 */
	public static $cache = array(
		'driver'      => 'Blackhole',
		'default_ttl' => 3600,
	);
}
