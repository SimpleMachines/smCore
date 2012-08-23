<?php

/**
 * smCore Request Class
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

namespace smCore;

use Inspekt_Cage;

class Request
{
	protected $_app;

	protected $_url;
	protected $_method = 'GET';
	protected $_is_xml_http_request = false;
	protected $_path;
	protected $_format;
	protected $_has_get_params = false;
	protected $_subdomain = 'www';

	public function __construct(Application $app)
	{
		$this->_app = $app;

		$this->_method = $this->_app['input']->server->getAlpha('REQUEST_METHOD');
		$this->_is_xml_http_request =
			$this->_app['input']->server->getAlpha('X_REQUESTED_WITH') == 'XMLHttpRequest'
			|| $this->_app['input']->get->keyExists('xmlHttpRequest')
			|| $this->_app['input']->post->keyExists('xmlHttpRequest')
		;

		// Get the app-relative path requested
		$this->_parsePath();
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function getPath()
	{
		return $this->_path;
	}

	public function getRequestMethod()
	{
		return $this->_method;
	}

	public function getFormat()
	{
		return $this->_format;
	}

	public function getSubdomain()
	{
		return $this->_subdomain;
	}

	public function isXmlHttpRequest()
	{
		return $this->_is_xml_http_request;
	}

	public function hasGetParams()
	{
		return $this->_has_get_params;
	}

	public function _parsePath()
	{
		if (isset($_SERVER['HTTP_X_REWRITE_URL']))
		{
			// IIRF rewrites for IIS
			$this->_url = $_SERVER['HTTP_X_REWRITE_URL'];
		}
		else if (isset($_SERVER['REQUEST_URI']))
		{
			// This covers both Apache and nginx
			$this->_url = $_SERVER['REQUEST_URI'];
		}
		else if (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL']))
		{
			// Default IIS is checked after IIRF so that IIRF takes precedence
			$this->_url = $_SERVER['UNENCODED_URL'];
		}
		else
		{
			$this->_url = '';
		}

		if ($_SERVER['HTTP_HOST'] != $_SERVER['SERVER_NAME'])
		{
			$this->_subdomain = rtrim(str_replace($_SERVER['SERVER_NAME'], '', $_SERVER['HTTP_HOST']), '.');
		}

		$_GET = array();

		if (!empty($this->_url))
		{
			if (false !== strpos($this->_url, '?', 1))
			{
				$query = substr($this->_url, strpos($this->_url, '?', 1) + 1);
				$this->_path = substr($this->_url, 0, strpos($this->_url, '?', 1));

				if (!empty($query))
				{
					$this->_has_get_params = true;

					$parameters = explode(';', str_replace('&', ';', $query));

					foreach ($parameters as $parameter)
					{
						if (false !== strpos($parameter, '='))
						{
							$_GET[substr($parameter, 0, strpos($parameter, '='))] = substr($parameter, strpos($parameter, '=') + 1);
						}
						else
						{
							$_GET[$parameter] = '';
						}
					}
				}
			}
			else
			{
				$this->_path = $this->_url;
			}

			// Find out what format this request is in
			if (substr($this->_path, -5) === '.json')
			{
				$this->_format = 'json';
			}
			else if (substr($this->_path, -4) === '.xml')
			{
				$this->_format = 'xml';
			}
			else
			{
				$this->_format = '';
			}

			$this->_path = trim($this->_path, '/');

			$base = trim(parse_url($this->_app['settings']['url'], PHP_URL_PATH), '/');

			if (!empty($base) && 0 === strpos($this->_path, $base))
			{
				$this->_path = trim(substr($this->_path, strlen($base)), '/');
			}
		}

		// Rebuild the superglobals and the cages
		$_REQUEST = $_POST + $_GET;

		// Forget what $_GET actually says - overwrite it with our fake query string.
		$this->_app['input']->get = Inspekt_Cage::Factory($_GET, null, '_GET', false);
		$this->_app['input']->request = Inspekt_Cage::Factory($_REQUEST, null, '_GET', false);
	}
}