<?php

/**
 * smCore Security Crypt - Abstract
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

use smCore\Exception;

abstract class AbstractCrypt
{
	public function __construct()
	{
	}

	public static function getRandomBytes($length)
	{
		$length = (int) $length;

		if (function_exists('openssl_random_pseudo_bytes'))
		{
			$output = openssl_random_pseudo_bytes($length, $crypto_strong);

			if ($crypto_strong && strlen($output) === $length)
			{
				return $output;
			}
		}

		if (is_readable('/dev/urandom') && $f = fopen('/dev/urandom', 'rb'))
		{
			$output = fread($f, $length);
			fclose($f);

			// @todo: it isn't long enough yet, urandom might not have given us the amount of bytes we need
			return $output;
		}

		// @todo
		throw new Exception('getRandomBytes() fallback not yet implemented');
	}
}