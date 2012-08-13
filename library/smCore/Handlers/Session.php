<?php

/**
 * smCore Session Handler
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

namespace smCore\Handlers;

use smCore\Storage\Factory as StorageFactory, smCore\Settings;

/**
 * Session handler class, replacing PHP's session functions. Session information is stored in the database.
 */
class Session
{
	protected $_storage;

	/**
	 * Instantiate a session handler. This directly replaces the default PHP handlers.
	 *
	 * @return boolean Whether or not we were able to set the save handler. This can return false if the session is already started.
	 */
	public function __construct()
	{
		$this->_storage = Settings::SESSION_DB_DRIVEN ? 'Sessions\Database' : 'Sessions\File';

		return session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	}

	/**
	 * 
	 *
	 * @param string $save_path
	 * @param string $session_name
	 *
	 * @return boolean
	 */
	public function open($save_path, $session_name)
	{
		return true;
	}

	/**
	 * 
	 *
	 * @return boolean
	 */
	public function close()
	{
		return true;
	}

	/**
	 * 
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function read($id)
	{
		return StorageFactory::factory($this->_storage)->read($id);
	}

	/**
	 * 
	 *
	 * @param string $id
	 * @param string $data
	 *
	 * @return boolean
	 */
	public function write($id, $data)
	{
		// All nice and dandy?
		if (preg_match('~^[A-Za-z0-9]{16,32}$~', $id) == 0)
		{
			return false;
		}

		return StorageFactory::factory($this->_storage, false)->write($id, $data);
	}

	/**
	 * 
	 *
	 * @param string $id
	 *
	 * @return boolean
	 */
	public function destroy($id)
	{
		// Just delete the row...
		return StorageFactory::factory($this->_storage, false)->destroy($id);
	}

	/**
	 * 
	 *
	 * @param int $max_lifetime
	 *
	 * @return bool
	 */
	public function gc($max_lifetime)
	{
		// We're going to ignore the max session lifetime
		StorageFactory::factory($this->_storage)->deleteExpired();
	}
}