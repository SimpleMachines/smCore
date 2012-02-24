<?php

/**
 * smCore Exception Class
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

class Exception extends \Exception
{
	/**
	 * Construct the exception
	 *
	 * @param  mixed $msg
	 * @param  int $code
	 * @param  Exception $previous
	 * @return void
	 */
	public function __construct($msg = '', $code = 0, Exception $previous = null)
	{
		if (!empty($msg) && Application::get('lang') !== null)
		{
			// If it's an array, we have replacements to send along
			if (is_array($msg))
			{
				$key = array_shift($msg);
				$msg = Application::get('lang')->get($key, $msg);
			}
			else
				$msg = Application::get('lang')->get($msg);
		}
		else if (is_array($msg))
			$msg = var_export($msg, true);

		parent::__construct($msg, (int) $code, $previous);
	}
}