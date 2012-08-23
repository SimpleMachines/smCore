<?php

/**
 * smCore Sessions Storage - Database
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

class Database extends AbstractStorage
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

		$db = $this->_app['db'];

		$result = $db->query("
			SELECT *
			FROM {db_prefix}sessions
			WHERE id_session = {string:id}
				AND session_expires > {int:time}",
			array(
				'id' => $id,
				'time' => time(),
			)
		);

		if ($result->rowCount() < 1)
		{
			return false;
		}

		$row = $result->fetch();
		return $row['session_data'];
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
		$db = $this->_app['db'];

		$result = $db->query("
			SELECT session_expires
			FROM {db_prefix}sessions
			WHERE id_session = {string:id}",
			array(
				'id' => $id,
			)
		);

		if ($result->rowCount() > 0)
		{
			$row = $result->fetch();
			$expires = (int) $row['session_expires'];
		}
		else
		{
			$expires = time() + $this->_app['session']->getLifetime();
		}

		$db->query("
			REPLACE INTO {db_prefix}sessions
				(id_session, session_data, session_expires)
			VALUES
				({string:id}, {string:data}, {int:expires})",
			array(
				'id' => $id,
				'data' => $data,
				'expires' => $expires,
			)
		);
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

		$this->_app['db']->query("
			DELETE FROM {db_prefix}sessions
			WHERE id_session = {string:id}",
			array(
				'id' => $id,
			)
		);

		return true;
	}

	/**
	 * Remove all expired sessions from the database. Used by the garbage collector.
	 */
	public function deleteExpired()
	{
		$this->_app['db']->query("
			DELETE FROM {db_prefix}sessions
			WHERE session_expires < {int:time}",
			array(
				'time' => time(),
			)
		);
	}
}