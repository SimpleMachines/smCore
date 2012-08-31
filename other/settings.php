<?php

class Settings extends smCore\Settings
{
	protected $_settings = array(
		/**
		 * These values should not include trailing slashes.
		 *
		 * PATH       Path where index.php is located
		 * MODULE_DIR Path to the /modules/ directory
		 * THEME_DIR  Path to the /themes/ directory
		 * CACHE_DIR  File cache directory, used by the Twig template cache and the File cache if enabled
		 */
		'path'              => '/home/my_site/public_html',
		'module_dir'        => '/home/my_site/public_html/modules',
		'theme_dir'         => '/home/my_site/public_html/themes',
		'cache_dir'         => '/home/my_site/public_html/cache',

		/**
		 * Again, no slashes at the end - http://www.example.com/smcore *not* http://www.example.com/smcore/
		 */
		'url'               => 'http://www.youdidntchangeyoursettingsfile.lol',

		/**
		 * You'll probably only need to configure COOKIE_DOMAIN
		 */
		'cookie_path'       => '/',
		'cookie_name'       => 'smcore_login',
		'cookie_domain'     => 'smcore.mysite.com',

		/**
		 * Use database-driven sessions instead of file-based sessions?
		 */
		'session_driver'    => 'Database',

		/**
		 * @todo: Default time zone should be a database setting and only used to display times, not store them
		 */
		'timezone'          => 'America/Los_Angeles',

		/**
		 * This string is used so multiple smCore installations don't interfere with each other
		 */
		'site_key'          => '07h8fAN4',
	
		'default_lang'      => 'en_US',
		'default_theme'     => 1,

		'mail_from'         => 'noreply@mysite.com',
		'mail_from_name'    => 'smCore',

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
		'database' => array(
			'driver'   => 'PDOMySql',
			'host'     => 'localhost',
			'user'     => '',
			'password' => '',
			'dbname'   => '',
			'prefix'   => 'smcore_',
		),

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
		'cache' =>  array(
			'driver'      => 'Blackhole',
			'default_ttl' => 3600,
		),

		'debug' => true,
	);
}