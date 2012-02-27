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
 *
 * @version 1.0 alpha
 */

namespace smCore\storage;
use smCore\model\Storage, smCore\Exception, smCore\events\Event;

/**
 * Database layer of Configuration storage.
 */
class ConfigStorage
{
	/**
	 * Read data from settings table.
	 *
	 * @return array
	 */
	function readConfig()
	{
		// @todo add cache'n stuff, adapt.
		$request = Storage::query('
			SELECT variable, value FROM {db_prefix}settings',
			array(
			)
		);
		$configData = array();

		if (!$request)
			throw new Exception('core_database_error');
        while ($row = Storage::fetch_row($request))
			$configData[$row[0]] = $row[1];
		Storage::free_result($request);

		// This isn't really appropriate in a storage class... but since for this we bypass everything...
		$event = new Event(null, 'core_config_loaded', $configData);
		$event->fire();

		return $configData;
	}

	/**
	 * Write configuration data.
	 *
	 * @param $data
	 * @return bool
	 */
	function writeConfig($data)
	{
		// @todo write $data in settings table.
	}
}