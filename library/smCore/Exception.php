<?php

/**
 * smCore platform
 *
 * @package smCore
 * @author smCore project
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
use smCore\Language;

/**
 * Runtime exception, it is thrown in many non-critical situations.
 */
class Exception extends \Exception
{
	/**
	 * Construct the exception. Takes an optional, additional parameter, $area, to specify
	 * the package the exception occurs in. May be used later to construct the message.
	 * i.e. $area='validation', or 'security', may be used to add information to the final message.
	 *
	 * @param  mixed $msg
	 * @param  int $code
	 * @param \Exception|null|\smCore\Exception $previous
	 * @param string $area=null
	 * @return \smCore\Exception
	 */
	public function __construct($msg = '', $code = 0, \Exception $previous = null, $area = null)
	{
		if (!empty($msg) && Language::getLanguage() !== null)
		{
			// If it's an array, we have replacements to send along
			if (is_array($msg))
			{
				$key = array_shift($msg);
				$msg = Language::getLanguage()->get($key, $msg);
			}
			else
				$msg = Language::getLanguage()->get($msg);
		}

		parent::__construct($msg, (int) $code, $previous);
	}
}