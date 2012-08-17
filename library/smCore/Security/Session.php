<?php

/**
 * smCore Session
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

namespace smCore\Security;

use smCore\Application, smCore\Handlers\Session as SessionHandler;

class Session
{
	protected static $_started = false;
	protected static $_lifetime = 3600;

	protected static function _overrideIni()
	{
		$settings = Application::get('settings');

		// @todo pay attention to safe mode set.
		ini_set('arg_separator.output', '&amp;');
		ini_set('session.gc_probability', '1');
		ini_set('session.use_cookies', true);
		ini_set('session.use_only_cookies', false);
		ini_set('session.use_trans_sid', false);
		ini_set('url_rewriter.tags', '');

		ini_set('session.cookie_path', '/');
		ini_set('session.cookie_secure', false);
		ini_set('session.cookie_httponly', true);
		ini_set('session.cookie_domain', $settings['cookie_domain']);
	}

	public static function start()
	{
		// Only start if it wasn't started yet.
		if (false === self::$_started)
		{
			// We should start anew. What about when PHP already started? Kill it.
			if (1 == ini_get('session.auto_start'))
			{
				session_write_close();
			}

			// Override ini parameters, if we can. That will happen both with default and user-provided options.
			self::_overrideIni();

			// Create the session handler, it will register itself to PHP.
			new SessionHandler();

			$settings = Application::get('settings');

			// Go!
			session_name($settings['cookie_name']);
			session_start();

			self::$_started = true;
		}
	}

	public static function end()
	{
		$settings = Application::get('settings');

		unset($_SESSION['id_user']);
		session_destroy();
		setcookie($settings['cookie_name'], '', 0, $settings['cookie_path'], $settings['cookie_domain']);
	}

	public static function reinitialize()
	{
		// Keep the data itself, we will need it.
		$old_data = $_SESSION;
		$_SESSION = array();
		session_destroy();

		// Recreate and restore the new session.
		self::start();
		session_regenerate_id();
		$_SESSION = $old_data;
	}

	public static function exists()
	{
		$settings = Application::get('settings');
		$cookie = Application::get('input')->cookie->getRaw($settings['cookie_name']);

		if (empty($cookie))
		{
			return false;
		}

		return true;
	}

	/**
	 * Set the lifetime for any new sessions created.
	 *
	 * @param int $length
	 */
	public static function setLifetime($length)
	{
		self::$_lifetime = max(0, (int) $length);
		session_set_cookie_params(self::$_lifetime);
	}

	/**
	 * 
	 *
	 * @return int
	 */
	public static function getLifetime()
	{
		return self::$_lifetime;
	}
}