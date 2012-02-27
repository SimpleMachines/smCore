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
 *
 */

namespace smCore\security;

/**
 * Validate the session.
 * i.e. Clean PHPSESSIDs, etc. May use makeValid() method to clean what it can, and otherwise responds to isValid()
 * with the validation result.
 */
class SessionValidator implements Validator
{

	/**
	 * Instantiate a session validator.
	 *
	 */
	function __construct()
	{
	}

	/**
	 * Validate the data passed to it, according to the rules specific to the classes implementing it.
	 *
	 * @param $toCheck the parameter/data to check
	 * @return bool
	 */
	function isValid($toCheck)
	{
		// TODO: Implement isValid() method.
		if (isset($_SESSION['USER_AGENT']))
			return $_SESSION['USER_AGENT'] === $toCheck;
	}

	/**
	 * Try to make the data valid, if possible. This method throws an exception if it can't perform the task.
	 *
	 * @throws Exception
	 */
	function makeValid()
	{
		// TODO: Implement makeValid() method.
	}

	function setUserAgent()
	{
		// For session check verification: don't switch browsers.
		$_SESSION['USER_AGENT'] = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
	}

}