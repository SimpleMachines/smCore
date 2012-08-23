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

use smCore\Application;

class Session
{
	protected $_app;

	protected $_started = false;
	protected $_lifetime = 3600;

	public function __construct(Application $app)
	{
		$this->_app = $app;
	}

	protected function _overrideIni()
	{
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
		ini_set('session.cookie_domain', $this->_app['settings']['cookie_domain']);
	}

	public function start()
	{
		// Only start if it wasn't started yet.
		if (false === $this->_started)
		{
			// We should start anew. What about when PHP already started? Kill it.
			if (1 == ini_get('session.auto_start'))
			{
				session_write_close();
			}

			// Override ini parameters, if we can. That will happen both with default and user-provided options.
			$this->_overrideIni();

			// Go!
			session_name($this->_app['settings']['cookie_name']);
			session_start();

			$this->_started = true;
		}
	}

	public function end()
	{
		unset($_SESSION['id_user']);
		session_destroy();
		setcookie($this->_app['settings']['cookie_name'], '', 0, $this->_app['settings']['cookie_path'], $this->_app['settings']['cookie_domain']);
	}

	public function reinitialize()
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

	public function exists()
	{
		$cookie = $this->_app['input']->cookie->getRaw($this->_app['settings']['cookie_name']);

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
	public function setLifetime($length)
	{
		$this->_lifetime = max(0, (int) $length);
		session_set_cookie_params($this->_lifetime);
	}

	/**
	 * 
	 *
	 * @return int
	 */
	public function getLifetime()
	{
		return $this->_lifetime;
	}
}