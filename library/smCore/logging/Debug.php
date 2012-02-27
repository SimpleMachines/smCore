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

/**
 * Debug class: debugging system main class. Allows to collect debug information.
 * It may be used by the Response to add to the output.
 */
class Debug
{
	private static $_messages = array();

	/**
	 * Add a message to the debug information being collected.
	 *
	 * @static
	 * @param $message
	 */
	public static function add($message)
	{
		self::$_messages[] = $message;
	}

	public static function output()
	{
		// may output messages on the specified or default medium
		// log to a log file
	}

	/**
	 * Return all debug messages collected.
	 *
	 * @static
	 * @return array
	 */
	public static function getMessages()
	{
		return self::$_messages;
	}
}
