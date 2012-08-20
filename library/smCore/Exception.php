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
	protected $_raw_message;

	public function __construct($exception, $code = 0, $previous = null)
	{
		if (is_array($exception))
		{
			$this->_raw_message = $exception;
			$exception = $exception[0];
		}
		else
		{
			$this->_raw_message = $exception;
		}

		parent::__construct($exception, $code, $previous);
	}

	public function getRawMessage()
	{
		return $this->_raw_message;
	}
}