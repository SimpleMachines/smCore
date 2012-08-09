<?php

/**
 * smCore Cache Driver - Black Hole
 *
 * The black hole driver does not save anything - it should be used only for development, never in production.
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

namespace smCore\Cache\Driver;

class Blackhole extends AbstractDriver
{
	public function __construct($options)
	{
	}

	public function load($key)
	{
		return false;
	}

	public function save($key, $data, $ttl = null)
	{
	}

	public function test($key)
	{
		return false;
	}

	public function remove($key)
	{
		return true;
	}

	public function clean($mode, array $tags = array())
	{
	}

	public function getMetadata($key)
	{
	}
}