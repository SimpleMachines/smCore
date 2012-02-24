<?php

/**
 * smCore Sessions Storage
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

namespace smCore\Storage;
use smCore\Application, smCore\Security\Session;

class Sessions
{
	/**
	 * Read a session from the database by ID.
	 *
	 * @param string $id
	 * @return mixed A Session object if a valid session was found, false otherwise.
	 *
	 * @access public
	 */
	public function read($id)
	{
		if (empty($id) || !preg_match('/^[a-z0-9]{16,32}$/i', $id))
			return false;

		$db = Application::get('db');

		$result = $db->query("
			SELECT *
			FROM beta_sessions
			WHERE id_session = ?
				AND session_expires > ?",
			array(
				$id,
				Application::get('time'),
			)
		);

		if ($result->rowCount() < 1)
			return false;

		return $result->fetch()->session_data;
	}

	/**
	 * Save a session to the database. I don't like that this does two queries.
	 *
	 * @param string $id
	 * @param string $data
	 * @param int $expires
	 * @return boolean
	 *
	 * @access public
	 */
	public function write($id, $data)
	{
		$db = Application::get('db');

		$result = $db->query("
			SELECT session_expires
			FROM beta_sessions
			WHERE id_session = ?",
			array(
				$id,
			)
		);

		if ($result->rowCount() > 0)
		{
			$expires = $result->fetch()->session_expires;
		}
		else
		{
			$expires = Application::get('time') + Session::getLifetime();
		}

		$db->query("
			REPLACE INTO beta_sessions
				(id_session, session_data, session_expires)
			VALUES
				(?, ?, ?)",
			array(
				$id,
				$data,
				$expires,
			)
		);
	}

	/**
	 * Remove a session from the database.
	 *
	 * @param string $id
	 * @return boolean
	 *
	 * @access public
	 */
	public function destroy($id)
	{
		if (empty($id) || !preg_match('/^[a-z0-9]{16,32}$/i', $id))
			return false;

		Application::get('db')->query("
			DELETE FROM beta_sessions
			WHERE id_session = ?",
			array(
				$id,
			)
		);

		return true;
	}

	/**
	 * Remove all expired sessions from the database. Used by the garbage collector.
	 *
	 * @access public
	 */
	public function deleteExpired()
	{
		Application::get('db')->query("
			DELETE FROM beta_sessions
			WHERE session_expires < ?",
			array(
				Application::get('time'),
			)
		);
	}
}