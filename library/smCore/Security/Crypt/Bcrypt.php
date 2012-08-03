<?php

/**
 * smCore Security Crypt - Bcrypt
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

namespace smCore\Security\Crypt;

class Bcrypt extends AbstractCrypt
{
	// @todo: Find a good default value.
	const DEFAULT_WORK_FACTOR = 12;

	protected $_bcryptModes = array('2a', '2x', '2y');
	protected $_defaultMode = '2y';

	public function __construct()
	{
		parent::__construct();

		// Versions before 5.3.7 only have the less-secure 2a mode.
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			$this->_bcryptModes = array('2a');
			$this->_defaultMode = '2a';
		}
	}

	public function encrypt($data, $work_factor = 0, $mode = null)
	{
		if ($work_factor < 4 || $work_factor > 31)
		{
			$work_factor = self::DEFAULT_WORK_FACTOR;
		}

		if (!in_array($mode, $this->_bcryptModes))
		{
			$mode = $this->_defaultMode;
		}

		// It wants 22 characters of a base64-like string, but with dots instead of plus signs.
		$bytes = substr(str_replace('+', '.', base64_encode($this->getRandomBytes(16))), 0, 22);

		$salt = sprintf('$%s$%02d$%s', $mode, $work_factor, $bytes);

		return crypt($data, $salt);
	}

	public function match($data, $encrypted)
	{
		return crypt($data, $encrypted) === $encrypted;
	}
}