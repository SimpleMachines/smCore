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

namespace smCore\logging;
use smCore\events\Event, smCore\Configuration, smCore\Request, smCore\storage\CoreStorage, smCore\model\User;

/**
 * Logger
 * This default logger logs errors, such as database errors.
 */
class Logger
{
	private $_errorTypes = array(
		'general',
		'critical',
		'database',
		'undefined_vars',
		'user',
		'template',
		'debug',
	);

	private $_lastError = null;

	/**
	 * Initialize the logger.
	 */
	private function __construct()
	{
		// Notify of initialization, for modules to add their data.
		// Such as error types.
		$event = new Event(null, 'error_types_initialized', &$this->_errorTypes);
		$event->fire();
	}

	/**
	 * Log the error.
	 * It should check if error logging is enabled for the respective module (though this can be globalized too).
	 * For now it uses the Config option used by SMF for enabling error logging.
	 * This implementation is a rewrite of SMF's log_error().
	 *
	 * @param string $error_message
	 * @param string $error_type = 'general'
	 * @param string $file = null __FILE__
	 * @param int $line = null __LINE__
	 * @return string, the error message
	 */
	public function logError($error_message, $error_type = 'general', $file = null, $line = null)
	{
		// Check if error logging is actually on.
		if (empty(Configuration::getConf()->enableErrorLogging))
			return $error_message;

		$error_message = htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8', false);
		$error_message = strtr($error_message, array("\n" => '<br />'));

		// File, line
		$file = ($file === null) ? '': str_replace('\\', '/', $file);
		$line = ($line === null) ? 0 : (int) $line;

		$query_string = Request::getInstance()->getQueryString();

		// Make sure the category that was specified is a valid one
		$error_type = in_array($error_type, $this->_errorTypes) ? $error_type : 'general';

		// Don't log the same error countless times, as we can get in a cycle of depression...
		$error_info = array(User::getCurrentUser()->get('id'), time(), Request::getInstance()->getClientIp(), $query_string, $error_message, $error_type, $file, $line);
		if (empty($this->_lastError) || $this->_lastError != $error_info)
		{
			CoreStorage::logError($error_info);
			$this->_lastError = $error_info;
		}

		// Return the message to make things simpler.
		return $error_message;
	}
}
