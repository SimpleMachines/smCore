<?php

/**
 * smCore platform
 *
 * @package smCore
 * @author Norv
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
 *
 */

namespace smCore;
use smCore\security\Session, smCore\security\SessionValidator, smCore\events\Event, smCore\Configuration;

/**
 * The Response to be sent to the client.
 * This abstraction serves to gather all parts of the response, buffer the output, and send it over.
 * It will also allow filtering and transformation of the output. (not implemented)
 */
class Response
{
	private static $instance = null;
	private $_headers = array();
	private $_meta = array();
	private $_body = null;

	const HTTP_503 = 'HTTP/1.1 503 Service Temporarily Unavailable';
	const SAMEORIGIN = 'X-Frame-Options: SAMEORIGIN';
	const XCONTENTPOLICY = "X-Content-Security-Policy: allow 'self'; options inline-script; frame-ancestors 'none'";
	const TEXT_HTML_UTF8 = 'Content-Type: text/html; charset=UTF-8';

	/**
	 * Constructor for the Response class.
	 */
	protected function __construct()
	{
		// default headers

		// Security.
		$this->addHeader(self::SAMEORIGIN);
		$this->addHeader(self::XCONTENTPOLICY);

		// Search engines leak prevention
		// Do not let search engines index anything if there is something $_GET.
		if (Request::getInstance()->hasGetParams())
			$this->_meta[] = '<meta name="robots" content="noindex" />';
	}

	/**
	 * Headers to be sent
	 * @return array
	 */
	function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * Add a HTTP header to the Response.
	 *
	 * @param $header
	 */
	function addHeader($header)
	{
		// This shouldn't be needed, but just in case
		if (!is_array($this->_headers))
			$_headers = array();

		// we'll have to factor this method nicely, let it be for the moment, to see all the needs here.
		if ($header === self::HTTP_503)
			$_headers[] = (self::HTTP_503);
	}

	/**
	 * Body of the response.
	 * @return string
	 */
	function getBody()
	{
		return $this->_body;
	}

	/**
	 * Send the output to the browser. End this execution.
	 * (also known as obExit() in SMF.)
	 */
	function sendOutput()
	{
		// @todo

		// SMF allows strict (do we really need this?)
		if (!empty(Configuration::getConf()->strict_doctype))
		{
			$temp = ob_get_contents();
			ob_clean();

			echo strtr($temp, array(
				'var smf_iso_case_folding' => 'var target_blank = \'_blank\'; var smf_iso_case_folding',
				'target="_blank"' => 'onclick="this.target=target_blank"'));
		}

		// @todo send an event here?

		exit;
	}

	/**
	 * Allows to make sure the browser doesn't come back and repost the form data.
	 *
	 * @param string $location = ''
	 * @param bool $refresh = false
	 */
	function redirect($location = '', $refresh = false)
	{
		// Some may be interested...
		$event = new Event(null,'core.redirect', array(&$location, &$refresh));
		$event->fire();

		// Refresh header if needed.
		if ($refresh)
			header('Refresh: 0; URL=' . strtr($location, array(' ' => '%20')));
		else
			header('Location: ' . str_replace(' ', '%20', $location));

		// Debugging.
		if (Application::debugMode())
			$_SESSION['debug_redirect'] = $db_cache;

		$this->sendOutput();
	}

	/**
	 * This method changes the output URLs if necessary.
	 * (it plays the role of ob_sessrewrite() in SMF).
	 *
	 * @param string $buffer
	 * @return string
	 */
	function rewriteUrls($buffer)
	{
		// Send an event for modules/plugins to hook into
		$event = new Event(null, 'core.rewrite_urls', &$buffer);
		$event->fire();

		// Return the changed buffer.
		return $buffer;
	}

	/**
	 * No clones allowed.
	 */
	final private function __clone(){}

	/**
	 * Only one Response, singleton pattern.
	 *
	 * @return \smCore\Response
	 */
	final public static function getInstance()
	{
		if (empty($instance))
			$instance = new Response();

		return $instance;
	}
}