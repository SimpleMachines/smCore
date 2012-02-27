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
use smCore\model\Storage;

/**
 * Database layer of Session handler.
 */
class SessionStorage
{
    /**
     * Read session info for $sessionId
     *
     * @param $sessionId
     * @return mixed
     */
	function readSessionData($sessionId)
	{
		$result = Storage::database()->query('
			SELECT data
			FROM {db_prefix}sessions
			WHERE session_id = {string:sessionId}
			LIMIT 1',
                 array(
                    'sessionId' => $sessionId,
                )
        );
		$sess_data = Storage::database()->fetch_row($result);
		return $sess_data;
	}

	/**
	 * Write session info for $sessionId
	 *
	 * @param $sessionId
	 * @param $data
	 * @return bool
	 */
	function writeSessionData($sessionId, $data)
	{
        if (preg_match('~^[A-Za-z0-9]{16,32}$~', $sessionId) == 0)
            return false;

        // First try to update an existing row...
        $result = Storage::database()->query('
		  UPDATE {db_prefix}sessions
		  SET data = {string:data}, last_update = {int:last_update}
		  WHERE session_id = {string:session_id}',
            array(
                'last_update' => time(),
                'data' => $data,
                'session_id' => $sessionId,
            )
        );

        // If that didn't work, try inserting a new one.
        if (Storage::database()->affected_rows() == 0)
            $result = Storage::database()->insert('ignore',
                '{db_prefix}sessions',
                array('session_id' => 'string', 'data' => 'string', 'last_update' => 'int'),
                array($sessionId, $data, time()),
                array('session_id')
            );

        return $result;
	}

	/**
	 * Delete info for $sessionId
	 *
	 * @param $sessionId
	 * @return bool
	 */
	function destroySessionData($sessionId)
	{
        if (preg_match('~^[A-Za-z0-9]{16,32}$~', $sessionId) == 0)
            return false;

        // Just delete the row...
        return Storage::database()->query('
		  DELETE FROM {db_prefix}sessions
		  WHERE session_id = {string:sessionId}',
            array(
                'sessionId' => $sessionId,
            )
        );
	}

    /**
     * Garbage collection for expired sessions.
     *
     * @param $maxLifetime
     * @return bool
     */
	function cleanupSessionData($maxLifetime)
	{
		// Clean up after yerself ;).
        return Storage::database()->query('
		  DELETE FROM {db_prefix}sessions
		  WHERE last_update < {int:lastUpdate}',
            array(
                'lastUpdate' => time() - $maxLifetime,
            )
        );
    }
}