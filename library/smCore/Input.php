<?php

/**
 * smCore Input Validation Class
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

use ArrayAccess, ArrayIterator, Countable, IteratorAggregate;
use FILTER_VALIDATE_IP;

class Input implements ArrayAccess, Countable, IteratorAggregate
{
	protected $_data;
	protected $_regexes;

	public function __construct(array $data = array(), $normalize_keys = false)
	{
		if ($normalize_keys)
		{
			foreach ($data as $key => $value)
			{
				$this->_data[strtolower($key)] = $value;
			}
		}
		else
		{
			$this->_data = $data;
		}

		$this->_regexes = array(
			'zip_us'   => '/^[0-9]{5}(?:\-[0-9]{4})?$/',
			'zip_ca'   => '/^[0-9][a-z][0-9][ -]?[a-z][0-9][a-z]$/i',
			'zip_uk'   => '',
			'float'    => '/^[+-]?(?:[0-9]{1,3})(?:,?[0-9]{3})*(?:\.[0-9]+)?(?:[eE]-?[0-9]+)?$/',
			'hostname' => '/^(?:[^\W_](?:[^\W_]|-){0,61}[^\W_]\.|[^\W_]\.)*(?:[^\W_](?:[^\W_]|-){0,61}[^\W_])\.?$/u',
			'phone'    => '/(?:\(\d{3}\) ?|\d{3} ?)?\d{3}[ -]?\d{4}/',
		);
	}

	/**
	 * Returns a raw value from the input data.
	 *
	 * @param string $key The key to use
	 *
	 * @return mixed The raw value from the input data, or false if the key doesn't exist
	 */
	public function getRaw($key)
	{
		if (isset($this->_data[$key]))
		{
			return $this->_data[$key];
		}

		return false;
	}

	public function keyExists($key)
	{
		return isset($this->_data[$key]);
	}

	public function getAlpha($key)
	{
		if (!isset($this->_data[$key]) || !$this->testAlpha($this->_data[$key]))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testAlpha($value)
	{
		return ctype_alpha((string) $value);
	}

	/**
	 * Validates and returns an integer from the input data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getInt($key)
	{
		if (!isset($this->_data[$key]) || !$this->testInt($this->_data[$key]))
		{
			return false;
		}

		return (int) $this->_data[$key];
	}

	public function testInt($value)
	{
		return ctype_digit((string) $value);
	}

	/**
	 * Validates and returns a float from the input data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getFloat($key)
	{
		if (!isset($this->_data[$key]) || !is_numeric($this->_data[$key]))
		{
			return false;
		}

		return (float) $this->_data[$key];
	}

	public function testFloat($value)
	{
		// PHP's internal filter for floats is terrible.
		return 1 === preg_match($this->_regexes['float'], (string) $value);
	}

	/**
	 * Validates and returns a hex value from the input data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getHex($key)
	{
		if (!isset($this->_data[$key]) || !$this->testHex($this->_data[$key]))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testHex($value)
	{
		return ctype_xdigit((string) $value);
	}

	/**
	 * Validates and returns a telephone number from the input data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getTelephone($key)
	{
		if (!isset($this->_data[$key]) || !$this->testTelephone($this->_data[$key]))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testTelephone($value)
	{
		if (1 === preg_match('/(?:\(\d{3}\) ?|\d{3} ?)?\d{3}[ -]?\d{4}/', $value))
		{
			return true;
		}

		// @todo: check if they added an extension (e1, ex2, ext3, x4)
		return false;
	}

	/**
	 * Validates and returns a zip code from the input data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getZip($key)
	{
		if (!isset($this->_data[$key]) || !$this->testZip($this->_data[$key]))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testZip($value)
	{
		return 1 === preg_match($this->_regexes['zip_us'], $value);
	}

	/**
	 * Validates and returns a hostname from the input data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getHostname($key)
	{
		if (!isset($this->_data[$key]) || !$this->testHostname($this->_data[$key]))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testHostname($value)
	{
		if (empty($value) || strlen($value) > 255)
		{
			return false;
		}

		if ($this->testIp($value))
		{
			return true;
		}

		if (0 === preg_match($this->_regexes['hostname'], $value))
		{
			return false;
		}

		return true;
	}

	/**
	 * Validates and returns an IP address from the input data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getIp($key)
	{
		if (!isset($this->_data[$key]) || !$this->testIp($this->_data[$key]))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testIp($value)
	{
		return false !== filter_var($value, FILTER_VALIDATE_IP);
	}

	/**
	 * Validates and returns an email address from the input data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getEmail($key)
	{
		if (!isset($this->_data[$key]) || !$this->testEmail($this->_data[$key]))
		{
			return false;
		}

		return $this->_data[$key];
	}

	/**
	 * Validate an email address.
	 *
	 * This only tests the domain portion, because despite what any RFC says, a mail server
	 * can do whatever it wants with the local portion. Validating on the RFC alone for now.
	 *
	 * @param mixed $value The email address to test
	 *
	 * @return boolean True if it looks like a valid email address, false otherwise
	 */
	public function testEmail($value)
	{
		if (false === strpos($value, '@', 1))
		{
			return false;
		}

		// Validate the domain only. Split this way because thisis"v@lid"@example.com
		$domain = end(explode('@', $value));

		// IP addreses can be wrapped in square brackets
		if (0 === strpos($domain, '[') && strlen($domain) === strpos($domain, ']'))
		{
			return $this->testIp(substr($domain, 1, -1));
		}

		return $this->testHostname($domain);
	}

	/**
	 * Returns a value from the input data, if it's in a set of options.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getIn($key, $options)
	{
		if (!isset($this->_data[$key]) || !$this->testOneOf($this->_data[$key], $options))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testIn($value, $options)
	{
		if (is_array($options))
		{
			return in_array($value, $options);
		}

		return false !== strpos((string) $options, (string) $value);
	}

	/**
	 * Returns a value from the input data, if it's greater than a certain value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getGreaterThan($key, $compare)
	{
		if (!isset($this->_data[$key]) || !$this->testGreaterThan($this->_data[$key], $compare))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testGreaterThan($value, $compare)
	{
		return $value > $compare;
	}

	/**
	 * Returns a value from the input data, if it's less than a certain value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getLessThan($key, $compare)
	{
		if (!isset($this->_data[$key]) || !$this->testLessThan($this->_data[$key], $compare))
		{
			return false;
		}

		return $this->_data[$key];
	}

	public function testLessThan($value, $compare)
	{
		return $value < $compare;
	}





	public function offsetExists($offset)
	{
		return isset($this->_data[$offset]);
	}

	public function offsetGet($offset)
	{
		if (isset($this->_data[$offset]))
		{
			return $this->_data[$offset];
		}
	}

	public function offsetSet($offset, $value)
	{
		$this->_data[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}

	public function getIterator()
	{
		return new ArrayIterator($this->_data);
	}

	public function count()
	{
		return count($this->_data);
	}
}