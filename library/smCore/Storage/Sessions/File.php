<?php

/**
 * smCore Sessions Storage - File
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

namespace smCore\Storage\Sessions;

use smCore\Security\Session, smCore\Storage\AbstractStorage;

class File extends AbstractStorage
{
	/**
	 * Read a session from the database by ID.
	 *
	 * @param string $id
	 *
	 * @return mixed A Session object if a valid session was found, false otherwise.
	 */
	public function read($id)
	{
		if (empty($id) || !preg_match('/^[a-z0-9]{16,32}$/i', $id))
		{
			return false;
		}

		$settings = $this->_app['settings'];

		$file = $settings['cache_dir'] . '/.smcore_session_' . $id;

		if (is_readable($file))
		{
			$data = unserialize(file_get_contents($file));

			if ($data['expires'] > time())
			{
				return $data['data'];
			}

			return false;
		}
	}

	/**
	 * Save a session to the database. I don't like that this does two queries.
	 *
	 * @param string $id
	 * @param string $data
	 * @param int    $expires
	 *
	 * @return boolean
	 */
	public function write($id, $data)
	{
		$settings = $this->_app['settings'];
		$file = $settings['cache_dir'] . '/.smcore_session_' . $id;
		$expires = time() + $this->_app['session']->getLifetime();

		// Get the old expiration time so we don't accidentally extend it
		if (is_readable($file))
		{
			$temp = unserialize(file_get_contents($file));

			if ($temp['expires'] > time())
			{
				$expires = $temp['expires'];
			}
		}

		$data = array(
			'expires' => $expires,
			'data' => $data,
		);

		// @todo: Lock/unlock file to prevent bad things
		file_put_contents($file, $data);
	}

	/**
	 * Remove a session from the database.
	 *
	 * @param string $id
	 *
	 * @return boolean
	 */
	public function destroy($id)
	{
		if (empty($id) || !preg_match('/^[a-z0-9]{16,32}$/i', $id))
		{
			return false;
		}

		@unlink($this->_app['settings']['cache_dir'] . '/.smcore_session_' . $id);

		return true;
	}

	/**
	 * Remove all expired sessions from the database. Used by the garbage collector.
	 */
	public function deleteExpired()
	{
		$sessions = glob($this->_app['settings']['cache_dir'] . '/.smcore_session_*');

		// @todo
	}
}