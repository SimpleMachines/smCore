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

namespace smCore\security;
use smCore\Exception;

/**
 * Validator interface.
 */
interface Validator
{
	/**
	 * Validate the data passed to it, according to the rules specific to the classes implementing it.
	 *
	 * @abstract
	 * @param $toCheck the parameter/data to check
	 * @return bool
	 */
	function isValid($toCheck);

	/**
	 * Try to make the data valid, if possible. This method throws an exception if it can't perform the task.
	 *
	 * @abstract
	 * @throws Exception
	 */
	function makeValid();

}