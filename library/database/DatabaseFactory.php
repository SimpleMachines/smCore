<?php

/**
 * Database layer
 *
 * @package database
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines and contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.0 Alpha 1
 */

namespace database;

/**
 * This class' job is to retrieve the adapter for the specific database system.
 */
class DatabaseFactory
{
	private static $_adapter;

	/**
	 * Create or get an adapter of the specific type for our database system.
	 *
	 * @param $adapter
	 * @param string $type - default options: 'default', 'extra', 'packages'.
	 * @internal param string $db_type - default options: 'mysql', 'postgresql', 'sqlite'
	 * (more can be aded)
	 * @return \database\DatabaseAdapter
	 */
	public static function getAdapter($adapter, $type = 'default')
	{
		assert (!empty($adapter));
		$adapter = strtolower($adapter);
		assert (in_array($adapter, array('mysql', 'postgresql', 'sqlite')));

		if (empty(self::$_adapter))
		{
			$adapterName = 'database\\' . ucfirst($type) . ucfirst($adapter) . 'Adapter';
			// if (file_exists($adapterName . '.php'))
			//	require_once($adapterName . '.php');

			self::$_adapter = new $adapterName();
		}
		return self::$_adapter;
	}
}