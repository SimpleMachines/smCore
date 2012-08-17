<?php

/**
 * smCore Error Handler
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

namespace smCore\Handlers;

class Error
{
	public function __construct()
	{
		set_error_handler(array($this, 'handle'));
	}

	/**
	 * Custom error handler
	 *
	 * @param int    $errno   Internal error number
	 * @param string $errstr  The error that was encountered
	 * @param string $errfile The file this error occurred in
	 * @param int    $errline The line this error occurred on
	 */
	public function handle($errno, $errstr, $errfile, $errline)
	{
		// This error code is not included in error_reporting
		if (!(error_reporting() & $errno))
		{
			return;
		}
//debug_print_backtrace();
		die('Error: ' . $errstr . ' in ' . $errfile . ' on line ' . $errline . '.');
	}
}