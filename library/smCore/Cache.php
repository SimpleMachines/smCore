<?php

/**
 * smCore Cache Class
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

class Cache
{
	public function load($key)
	{
	}

	public function save($key, $data, array $tags = array(), $lifetime = null)
	{
	}

	public function test($key)
	{
	}

	public function remove($key)
	{
	}

	public function clean($mode, array $tags = array())
	{
	}

	public function getMetadata($key)
	{
	}

	/**
	 * Normalize a cache key
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function _normalize($key)
	{
		return preg_replace('/[^a-z0-9_-]/i', '_', $key);
	}
}