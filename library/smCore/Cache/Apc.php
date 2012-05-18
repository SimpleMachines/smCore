<?php

/**
 * smCore 
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

namespace smCore\Cache;
use smCore\Cache;

class Apc extends Cache
{
	public abstract function load($key)
	{
	}

	public abstract function save($key, $data, array $tags = array(), $lifetime = null)
	{
	}

	public abstract function test($key)
	{
	}

	public abstract function remove($key)
	{
	}

	public abstract function clean($mode, array $tags = array())
	{
	}

	public abstract function getMetadata($key)
	{
	}
}