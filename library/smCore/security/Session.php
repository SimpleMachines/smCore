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

namespace smCore\security;
use smCore\handlers\SessionHandler, smCore\Configuration, Settings;

/**
 * Session class.
 */
class Session
{
	private static $_started = false;
	private static $_iniOptions = array();

	/**
	 * Try to set ini options, those we want to override by default as well as some provided by the user,
	 * if any.
	 */
	private static function overrideIni()
	{
		// @todo pay attention to safe mode set.
		ini_set('session.use_cookies', true);
		ini_set('session.use_only_cookies', false);
		ini_set('url_rewriter.tags', '');
		ini_set('session.use_trans_sid', false);
		ini_set('arg_separator.output', '&amp;');
		ini_set('session.gc_probability', '1');

		$cookie = Cookie::emptyCookie();
		ini_set('session.cookie_domain', '.' . $cookie->getDomain());

		if (!empty(self::$_iniOptions) && is_array(self::$_iniOptions))
		{
			// We have a bunch of options to override, 'cause the user says so.
			foreach (self::$_iniOptions as $option)
			{
				ini_set('session.' . $option[0], $option[1]);
			}
		}
	}

	/**
	 * @static
	 *
	 * Start the session, at least if, hopefully, it wasn't started already.
	 * This method makes sure to set ini options the user may have chosen, along
	 * with our defaults.
	 */
	static function startSession()
	{
		// Only start if it wasn't started yet.
		if (empty(self::$_started))
		{
			// We should start anew. What about when PHP already started? Kill it.
			if (ini_get('session.auto_start') == 1)
				session_write_close();

			// Override ini parameters, if we can. That will happen both with default and user-provided options.
			self::overrideIni();

			// Create the SessionHandler, it will register itself to PHP.
			new SessionHandler();

			// Go!
			session_start();

			// At this point, lets generate the session tokens like SMF expects.
			// ( @todo remove this when SMF works as module. )
			if (!isset($_SESSION['session_var']))
			{
				$_SESSION['session_value'] = md5(session_id() . mt_rand());
				$_SESSION['session_var'] = substr(preg_replace('~^\d+~', '', sha1(mt_rand() . session_id() . mt_rand())), 0, rand(7, 12));
			}

			self::$_started = true;
		}
	}

	/**
	 * Destroy the current session, and regenerate it.
	 * This method keeps the session data.
	 * Meant to be used when user's session needs reset for security reasons.
	 */
	static function reinitializeSession()
	{
		// Keep the data itself, we will need it.
		$oldSessionData = $_SESSION;
		$_SESSION = array();
		session_destroy();

		// Recreate and restore the new session.
		self::startSession();
		session_regenerate_id();
		$_SESSION = $oldSessionData;
	}

	/**
	 * Provide PHP ini options to override. $options should be an array of key/value pairs.
	 *
	 * @static
	 * @param array $options
	 */
	static function setIniOptions($options = array())
	{
		self::$_iniOptions = $options;
	}

	/**
	 * Sets a login cookie.
	 * The cookie is the same format as SMF's login cookie, same validation requirements, except it accepts secure
	 * and httponly flags. Additionally, it accepts "fakeURLs", as SMF calls them, though this should be reviewed,
     * for subdomains with particular module installations for example.
	 *
	 * @static
	 * @param $cookie_length
	 * @param $userId
	 * @param string $password
	 */
	static function setLoginCookie($cookie_length, $userId, $password = '')
	{
		// We don't need the old cookie, if any, so empty it out.
		$cookieName = Configuration::getConf()->getCookieName();
		if (Cookie::validateFromRequest())
		{
			// How odd, you're already here. No matter, out with you, and set you anew.
			setcookie($cookieName, Cookie::serialize(Cookie::emptyCookie()));
		}

		$cookie = Cookie::emptyCookie(); // note this will unset it from Request too.
		$cookie->initializeCookie($cookie_length, $userId, $password);
		setcookie($cookie->getName(), serialize($cookie->getData()), $cookie->getExpire(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure(), $cookie->getHttponly());

		// Alias URLs. This is for SMF compatibility, although we likely need to review this.
		// It concerns the "aliasUrls" defined as user setting, like SMF allows.
		$aliasUrls = Configuration::getConf()->getAliasUrls();
		if (!empty($aliasUrls))
		{
			$aliases = explode(',', $aliasUrls);

			foreach ($aliases as $alias)
			{
				// Reinit from URL for each cookie we want to set.
				// We only set http.
				$alias = strtr(trim($alias), array('http://' => '',
												  'https://' => ''));
				$cookie->initFromUrl('http://' . $alias);

				setcookie($cookie->getName(), serialize($cookie->getData()), $cookie->getExpire(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure(), $cookie->getHttponly());
			}
		}

		// Make sure the user logs in with a new session ID.
		if (!isset($_SESSION['login_' . $cookieName]) || $_SESSION['login_' . $cookieName] !== serialize($cookie->getData()))
		{
			self::reinitializeSession();
			$_SESSION['login_' . $cookieName] = serialize($cookie->getData());
		}
	}

	/**
	 * Returns whether the current user is logged in.
	 * Not the best idea to have this method here...
	 *
	 * @static
	 * @return bool
	 */
	public static function isLoggedIn()
	{
		return Cookie::validateFromSession();
	}

	/**
	 * Save the current value of the given $key in the $_SESSION.
	 * We might need it later!
	 *
	 * @static
	 * @param $key
	 * @return mixed
	 */
	static function save($key)
	{
		if(empty($key))
			return;
		switch ($key)
		{
			// These are SMF's variables, as needed by the 2.0 version.
			// We should actually replace them as SMF is being rewritten, and ceases using [some of] them.
			case 'login_url':
				// Do we happen to have an old_url already?
				// (not attachment)
				if (isset($_SESSION['old_url']) && strpos($_SESSION['old_url'], 'dlattach') === false && preg_match('~(board|topic)[=,]~', $_SESSION['old_url']) != 0)
					$_SESSION['login_url'] = $_SESSION['old_url'];
				elseif (isset($_SESSION['login_url']))
					unset($_SESSION['login_url']);
				// Never redirect to an attachment
				elseif (strpos($_SERVER['REQUEST_URL'], 'dlattach') === false && !preg_match('~/logout/?~', $_SERVER['REQUEST_URL']))
					$_SESSION['login_url'] = $_SERVER['REQUEST_URL'];
				break;
			case 'old_url':
				// in case someone doesn't like sending HTTP_REFERER.
				if (strpos($_SERVER['REQUEST_URL'], 'action=dlattach') === false && strpos($_SERVER['REQUEST_URL'], 'action=viewsmfile') === false)
					$_SESSION['old_url'] = $_SERVER['REQUEST_URL'];
				break;
			default:
				break;
		}
	}

	/**
	 * Retrieve $key from SESSION, if set anyway.
	 *
	 * @static
	 * @param $key
	 * @return mixed
	 */
	static function get($key)
	{
		if (empty($key))
			return null;
		if (isset($_SESSION[$key]))
			return $_SESSION[$key];
		return null;
	}

	/**
	 * Save an array of key/value pairs, in $_SESSION.
	 *
	 * @static
	 * @param $array
	 */
	static function saveArray($array)
	{
		if(empty($array) || (!is_array($array)))
			return;
		foreach ($array as $key => $value)
		{
			// Don't we want something fancy here
			$_SESSION[$key] = $value;
		}
	}

	/**
	 * Create the session object? No need.
	 */
	private function __construct()
	{
		// @todo
	}

	/**
	 * Check session
	 */
	static function checkSession()
	{
		// check validity of the user session
	}

	static function checkAdminSession()
	{
		// check admin session
	}

	static function checkModeratorSession()
	{
		// check validity/expiry of moderator session
	}
}