<?php

/**
 * smCore Response Class
 *
 * The Response to be sent to the client. This abstraction serves to gather all parts of the
 * response, buffer the output, and send it over. It will also allow filtering and
 * transformation of the output (not yet implemented).
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

class Response
{
	protected $_app;

	private $_headers = array();
	private $_meta = array();
	private $_body = null;

	// Some common response codes
	const HTTP_200 = 'HTTP/1.1 200 OK';
	const HTTP_204 = 'HTTP/1.1 204 No Content';
	const HTTP_301 = 'HTTP/1.1 301 Moved Permanently';
	const HTTP_302 = 'HTTP/1.1 302 Found';
	const HTTP_303 = 'HTTP/1.1 303 See Other';
	const HTTP_307 = 'HTTP/1.1 307 Temporary Redirect';
	const HTTP_403 = 'HTTP/1.1 403 Not Allowed';
	const HTTP_404 = 'HTTP/1.1 404 Not Found';
	const HTTP_503 = 'HTTP/1.1 503 Service Temporarily Unavailable';

	const SAMEORIGIN = 'X-Frame-Options: SAMEORIGIN';
	const XCONTENTPOLICY = "X-Content-Security-Policy: allow 'self'; options inline-script; frame-ancestors 'none'";
	const TEXT_HTML_UTF8 = 'Content-Type: text/html; charset=UTF-8';

	/**
	 * Constructor for the Response class.
	 */
	public function __construct(Application $app)
	{
		$this->_app = $app;

		// Security.
		$this
			->addHeader(self::SAMEORIGIN)
			->addHeader(self::XCONTENTPOLICY)
		;

		// Search engines leak prevention
		// Do not let search engines index anything if there is something in $_GET.
		if ($this->_app['request']->hasGetParams())
		{
			$this->_meta[] = '<meta name="robots" content="noindex" />';
		}
	}

	/**
	 * Headers to be sent
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * Add a HTTP header to the Response.
	 *
	 * @param string $header A text header to send.
	 */
	public function addHeader($header)
	{
		// We'll have to factor this method nicely, let it be for the moment, to see all the needs here.

		// @todo: Exception if the header has newlines in it. Big no no.
		$header = trim($header);

		// Only allow one HTTP response code. This helps us add simple headers via ->addHeader(404)
		if (ctype_digit($header) && defined('self::HTTP_' . $header))
		{
			$this->_headers['http_response_code'] = constant('self::HTTP_' . $header);
		}
		else if (0 === strpos($header, 'HTTP/1.1'))
		{
			$this->_headers['http_response_code'] = $header;
		}
		else
		{
			$this->_headers[] = $header;
		}

		return $this;
	}

	/**
	 * Clear all stored headers.
	 */
	public function clearHeaders()
	{
		$this->_headers = array();

		return $this;
	}

	/**
	 * Body of the response.
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->_body;
	}

	/**
	 * 
	 *
	 * @param string $body
	 */
	public function setBody($body)
	{
		$this->_body = $body;

		return $this;
	}

	/**
	 * Send the output to the browser. End this execution.
	 */
	public function sendOutput()
	{
		if (!empty($this->_headers))
		{
			foreach ($this->_headers as $header)
			{
				header($header);
			}
		}

		if (empty($this->_headers['http_response_code']))
		{
			header(self::HTTP_200);
		}

		echo $this->_body;

		die();
	}

	/**
	 * 
	 *
	 * @param string  $url       URL to redirect to
	 * @param boolean $permanent Redirect permanently (301) or temporarily (307)
	 */
	public function redirect($url = null, $permanent = false)
	{
		if (null === $url)
		{
			$url = $this->_app['settings']['url'];
		}
		else if (!preg_match('/^https?:\/\//', $url))
		{
			$url = $this->_app['settings']['url'] . '/' . ltrim($url, '/');
		}

		$this
			->addHeader(303)
			->addHeader('Location: ' . $url)
			->sendOutput()
		;
	}

	/**
	 * No clones allowed.
	 */
	final private function __clone(){}
}