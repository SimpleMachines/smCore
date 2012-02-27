<?php

/**
 * smCore platform
 *
 * Implementation of PHP's session API, using the Session::getSessionStorage().
 * (which is currently implemented as database storage in a similar table with SMF's sessions)
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

namespace smCore\handlers;
use smCore\model\Storage, Settings;

/**
 * Session handler class, replacing PHP's session functions. Session information is stored in the database.
 */
class SessionHandler
{
	/**
	 * Instantiate a session handler. It has no meaning to make more of them. This directly replaces the default
	 * PHP handlers.
	 */
	public function __construct()
	{
		session_set_save_handler(
			array($this, "open"),
			array($this, "close"),
			array($this, "read"),
			array($this, "write"),
			array($this, "destroy"),
			array($this, "gc")
		);
	}

	/**
	 * Implementation of sessionOpen() replacing the standard open handler.
	 * It simply returns true.
	 *
	 * @param string $save_path
	 * @param string $session_name
	 * @return bool
	 */
	function open($save_path, $session_name)
	{
		// $this->savePath = $savePath;
		// $this->sessionName = $sessionName;
		return true;
	}

	/**
	 * Implementation of sessionClose() replacing the standard close handler.
	 * It simply returns true.
	 *
	 * @return bool
	 */
	function close()
	{
		return true;
	}

	/**
	 * Implementation of sessionRead() replacing the standard read handler.
	 *
	 * @param string $sessionId
	 * @return string
	 */
	function read($sessionId)
	{
		// Just to be sure.
		// @todo move in a validator?
		if (preg_match('~^[A-Za-z0-9]{16,32}$~', $sessionId) == 0)
			return false;

		return Storage::getSessionStorage()->readSessionData($sessionId);
	}

	/**
	 * Implementation of sessionWrite() replacing the standard PHP write handler.
	 *
	 * @param string $sessionId
	 * @param string $data
	 * @return bool
	 */
	function write($sessionId, $data)
	{
		// All nice and dandy?
		if (preg_match('~^[A-Za-z0-9]{16,32}$~', $sessionId) == 0)
			return false;

		return Storage::getSessionStorage()->writeSessionData($sessionId, $data);
	}

	/**
	 * Implementation of sessionDestroy() replacing the standard destroy handler.
	 *
	 * @param string $sessionId
	 * @return bool
	 */
	function destroy($sessionId)
	{
		if (preg_match('~^[A-Za-z0-9]{16,32}$~', $sessionId) == 0)
			return false;

		// Just delete the row...
		return Storage::getSessionStorage()->destroySessionData($sessionId);
	}

	/**
	 * Implementation of sessionGC() replacing the standard gc handler.
	 * Callback for garbage collection.
	 *
	 * @param int $maxLifetime
	 * @return bool
	 */
	function gc($maxLifetime)
	{
		// Clean up after yerself ;).
		return Storage::getSessionStorage()->cleanupSessionData(time() - $maxLifetime);
	}
}
