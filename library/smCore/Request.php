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

class Request
{
	protected $_url;
	protected $_method = 'GET';
	protected $_is_xml_http_request = false;
	protected $_path;
	protected $_format;
	protected $_has_get_params = false;
	protected $_subdomain = 'www';

	protected $_base_url = '';

	protected $_start_time;

	public function __construct()
	{
		$this->_start_time = microtime(true);

		$this->_method = $_SERVER['REQUEST_METHOD'];

		$this->_is_xml_http_request =
			(isset($_SERVER['X_REQUESTED_WITH']) && $_SERVER['X_REQUESTED_WITH'] == 'XMLHttpRequest')
			|| isset($_GET['xmlHttpRequest'])
			|| isset($_POST['xmlHttpRequest'])
		;
	}

	public function setBaseUrl($url)
	{
		$this->_base_url = $url;

		return $this;
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

	public function parse()
	{
		if (isset($_SERVER['HTTP_X_REWRITE_URL']))
		{
			// IIRF rewrites for IIS
			$this->_path = $_SERVER['HTTP_X_REWRITE_URL'];
		}
		else if (isset($_SERVER['REQUEST_URI']))
		{
			// This covers both Apache and nginx
			$this->_path = $_SERVER['REQUEST_URI'];
		}
		else if (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL']))
		{
			// Default IIS is checked after IIRF so that IIRF takes precedence
			$this->_path = $_SERVER['UNENCODED_URL'];
		}
		else
		{
			$this->_path = '';
		}

		// If smCore is installed in a subdirectory, strip it
		$parsed_url = parse_url($this->_base_url);

		if (!empty($parsed_url['path']) && 0 === strpos($this->_path, $parsed_url['path']))
		{
			$this->_path = substr($this->_path, strlen($parsed_url['path']));
		}

		if ($_SERVER['HTTP_HOST'] != $_SERVER['SERVER_NAME'])
		{
			$this->_subdomain = rtrim(str_replace($_SERVER['SERVER_NAME'], '', $_SERVER['HTTP_HOST']), '.');
		}

		$_GET = array();

		if (!empty($this->_path))
		{
			if (false !== strpos($this->_path, '?', 1))
			{
				$query = substr($this->_path, strpos($this->_path, '?', 1) + 1);
				$this->_path = substr($this->_path, 0, strpos($this->_path, '?', 1));

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
				$this->_path = $this->_path;
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
		}

		// Rebuild the superglobals and the cages
		$_REQUEST = $_POST + $_GET;
	}

	public function getStartTime()
	{
		return $this->_start_time;
	}
}