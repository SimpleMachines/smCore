<?php

/**
 * smCore Cookie Model
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

namespace smCore\Model;
use smCore\Model, smCore\Settings, smCore\Request, smCore\Exception, smCore\Application;

class Cookie
{
	protected $_name;
	protected $_value;
	protected $_expire = 0;
	protected $_path;
	protected $_domain;
	protected $_secure = false;
	protected $_httponly = false;

	/**
	 * Default constructor for the Cookie class. The cookie will be initialized with name, domain, path from settings,
	 * the rest being empty values.
	 *
	 * @param string  $name         The name of this cookie.
	 * @param boolean $attempt_read If true, see if this cookie already exists and fetch its value.
	 */
	public function __construct($name, $attempt_read = true)
	{
		$input = Application::get('input');

		$this->_name = $name;
		$this->_domain = Settings::COOKIE_DOMAIN;
		$this->_path = Settings::COOKIE_PATH;

		// Does this cookie already exist?
		if ($attempt_read && $input->cookie->keyExists($name))
			$this->_value = $input->cookie->getRaw($name);
	}
















	/**
	 * If this is the main cookie, 
	 *
	 * @return boolean
	 */
	public function validateSession()
	{
		if ($this->_name !== Settings::COOKIE_NAME)
			return false;

		if ($this->_expire < Application::get('time'))
			return false;

		if ($this->_secure !== 0 && $this->_secure !== 1)
			return false;

		if ($this->_httponly !== 0 && $this->_httponly !== 1)
			return false;

		return true;
	}

	/**
	 * Validate the cookie passed from the Request.
	 * This method validates the SMF-style cookie.
	 *
	 * @return boolean
	 */
	public static function validateSessionCookie()
	{
		// First things first.
		$cookie_value = Application::get('input')->cookie->getRaw(Settings::COOKIE_NAME);

		if (empty($cookie_value))
			return false;

		// Lets see what you got here...
		return @unserialize($cookie_value);
	}

	/**
	 * Read the cookie from the request we got. Returns an instance of this class, initialized with the data
	 * sent to us.
	 *
	 * @static
	 * @return Cookie
	 */
	public static function readSessionCookie()
	{
		if (($data = self::validateFromRequest()) === false)
			throw new Exception('security_invalid_cookie', 0, null, 'security');

		$cookie = new self(Settings::COOKIE_NAME);

		$cookieValue = Request::getInstance()->getCookieValue($cookieName);
		$array = @unserialize($cookieValue);
		// user, password, time, state
		// or, 0, '', 0

		return $cookie;
	}

	/**
	 * Initialize the cookie with the domain and path for the url.
	 * Uses the localCookies and globalCookies settings, respectively.
	 * This method is reusing url_parts() function from SMF.
	 *
	 * @param string $fakeUrl=null optional parameter to specify an alternative URL if suited (i.e. for frames)
	 *
	 * @return array an array to set the cookie on with domain and path in it, in that order
	 */
	public function initFromUrl($fakeUrl = null)
	{
		// Parse the URL with PHP to make life easier.
		if (!empty($fakeUrl))
			$parsed_url = parse_url($fakeUrl);
		else
			$parsed_url = parse_url(Settings::APP_URL);

		$localCookies = Configuration::getConf()->getLocalCookies();
		$globalCookies = Configuration::getConf()->getGlobalCookies();

		// Is local cookies off?
		if (empty($localCookies))
			$parsed_url['path'] = '';

		// Globalize cookies across domains (filter out IP-addresses)
		if ($globalCookies && preg_match('~^\d{1,3}(\.\d{1,3}){3}$~', $parsed_url['host']) == 0 && preg_match('~(?:[^\.]+\.)?([^\.]{2,}\..+)\z~i', $parsed_url['host'], $parts) == 1)
			$parsed_url['host'] = '.' . $parts[1];

		// We shouldn't use a host at all if both options are off.
		elseif (!$localCookies && !$globalCookies)
			$parsed_url['host'] = '';

		// The host also shouldn't be set if there aren't any dots in it.
		elseif (!isset($parsed_url['host']) || strpos($parsed_url['host'], '.') === false)
			$parsed_url['host'] = '';

		$this->_domain = $parsed_url['host'];
		$this->_path = $parsed_url['path'] . '/';
	}

	/**
	 * Create and return an empty cookie, well-formed for the application.
	 * It has the default expected name, domain, path.
	 * Empty out the $_COOKIE too, if possible, to be sure.
	 *
	 * @return Cookie
	 */
	public static function emptyCookie()
	{
		// We can't use readFromRequest() here, it would end up in an endless loop because empty-ing is done
		// when the cookie is invalid (which readFromRequest() checks)
		$cookieName = Settings::COOKIE_NAME;
		$cookieValue = Request::getInstance()->getCookieValue($cookieName);

		if (!empty($cookieValue))
			Request::getInstance()->unsetCookieValue($cookieName);

		$cookie = new Cookie();
		return $cookie;
	}

	/**
	 * Initialize the cookie with the valid values, according to the parameters passed and configuration options.
	 * This method is guaranteed to result in an well-formed cookie, or may throw a security exception otherwise.
	 *
	 * @param int    $length
	 * @param int    $id_user
	 * @param string $password
	 * @param string $fakeUrl
	 */
	public function initializeCookie($length = 0, $id_user = 0, $password = '', $fakeUrl = null)
	{
		$this->_name = Settings::COOKIE_NAME;
		$this->_expire = time() + $length;
		if (!empty($id_user))
		{
			$localCookies = Configuration::getConf()->getLocalCookies();
			$globalCookies = Configuration::getConf()->getGlobalCookies();
			$cookieState = (empty($localCookies) ? 0 : 1) | (empty($globalCookies) ? 0 : 2);
			$this->_data = array($id_user, $password, time() + $length, $cookieState);
		}
		$this->_secure = Configuration::getConf()->getSecureCookies();
		$this->_httponly = Configuration::getConf()->getHttpOnly();
		$this->initFromUrl($fakeUrl);
	}

	public function getData()
	{
		return $this->_data;
	}

	public function getDomain()
	{
		return $this->_domain;
	}

	public function getExpire()
	{
		return $this->_expire;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function getPath()
	{
		return $this->_path;
	}

	public function getHttponly()
	{
		return $this->_httponly;
	}

	public function getSecure()
	{
		return $this->_secure;
	}
}