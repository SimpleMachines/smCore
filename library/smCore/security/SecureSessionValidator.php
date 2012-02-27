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
 * Validator for secure sessions (i.e. moderator session, admin session)
 */
class SecureSessionValidator implements Validator
{
	private $_type = null;
	private $_token = null;

	/**
	 * Create a secure session validator (as necessary for some actions)
	 *
	 * @param string $type
	 * @param string $token
	 */
	function __construct($type, $token)
	{
		$this->_type = $type;
		$this->_token = $token; // create it here?
	}

	/**
	 * Validate the data passed to it, according to the rules specific to the classes implementing it.
	 *
	 * @param $toCheck
	 * @return bool
	 */
	function isValid($toCheck)
	{
		// TODO: Implement isValid() method.
		// validate the token; clean up
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
}