<?php

/**
 * smCore platform
 *
 * Based on SMF authentication
 * @copyright 2011 Simple Machines and contributors
 *
 * @package smCore
 * @author Simple Machines contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.0 alpha
 */

namespace smCore\security;
use smCore\Configuration, Settings, smCore\Request, smCore\Exception, smCore\Application;

/**
 * Our good ole' Cookie.
 */
class Cookie
{
	private $_name = null;
	private $_domain = null;
	private $_path = null;
	private $_expire = null;
	private $_data = null;
	private $_secure = false;
	private $_httponly = false;

	/**
	 * Make me a Cookie.
	 * Default constructor for the Cookie class. The cookie will be initialized with name, domain, path from settings,
	 * the rest being empty values.
	 */
	public function __construct()
	{
		$this->_name = Settings::COOKIE_NAME;
		$this->_domain = Settings::COOKIE_DOMAIN;
		$this->_path = Settings::COOKIE_PATH;
		$this->_expire = time() - 3600;
		$this->_data = array(0, '', 0);
		$this->_secure = false;
		$this->_httponly = false;
	}

	/**
	 * Validate this cookie.
	 *
	 * @return bool
	 */
	public function validateCurrent()
	{
		if ($this->_name !== Settings::COOKIE_NAME)
			return false;
		if ($this->_expire < Application::getStartTime())
			return false;
		if ($this->_secure !== 0 && $this->_secure !== 1)
			return false;
		if ($this->_httponly !== 0 && $this->_httponly !== 1)
			return false;

		return false;
	}

	/**
	 * Validate the cookie passed from the Request.
	 * This method validates the SMF-style cookie.
	 *
	 * @return bool
	 */
	public static function validateFromRequest()
	{
		$cookieName = Settings::COOKIE_NAME;

		// First things first.
		$cookieValue = Request::getInstance()->getCookieValue($cookieName);
		if (empty($cookieValue) || !preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~', $cookieValue === 1))
			return false;

		// Lets see what you got here...
		// user, password, time, state
		// or, 0, '', 0
		return self::validate($cookieValue);
	}

	/**
	 * Read the cookie from the request we got. Returns an instance of this class, initialized with the data
	 * sent to us.
	 *
	 * @static
	 * @return Cookie
	 */
	public static function readFromRequest()
	{
		if (!self::validateFromRequest())
			throw new Exception('security_invalid_cookie', 0, null, 'security');
		$cookie = new Cookie();
		$cookieName = Settings::COOKIE_NAME;
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
	 * @static
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
	 * Serialize the cookie.
	 *
	 * @static
	 * @param Cookie $cookie
	 * @return string
	 */
	public static function serialize(Cookie $cookie)
	{
		return serialize($cookie->getData(), $cookie->getExpire(), $cookie->getPath(), $cookie->getDomain());
	}

	/**
	 * Initialize the cookie with the valid values, according to the parameters passed and configuration options.
	 * This method is guaranteed to result in an well-formed cookie, or may throw a security exception otherwise.
	 *
	 * @param int $length=0
	 * @param int $id_user=0
	 * @param string $password=''
	 * @param string $fakeUrl=null
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

	/**
	 * Validate the cookie of the current session.
	 * (for debugging purposes)
	 *
	 * @static
	 */
	public static function validateFromSession()
	{
		$cookieName = Configuration::getConf()->getCookieName();
		self::validate(unserialize($_SESSION['login_' . $cookieName]));
	}

	/**
	 * Validate the passed string of serialized cookie values.
	 *
	 * @static
	 * @param $string
	 * @return bool|int
	 */
	public static function validate($string)
	{
		// Validate the serialized $string of cookie values
		if (preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~i', $string) == 1)
		{
			list ($id_member, $password, $time, $state) = @unserialize($string);
			$id_member = !empty($id_member) && strlen($password) > 0 ? (int) $id_member : 0;
		}
		else
			$id_member = 0;
		if ($id_member === 0)
			return 0;

		return false;
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