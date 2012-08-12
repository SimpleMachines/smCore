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

	/**
	 * {@inheritdoc}
	 */
	public function load($key, $failure_return = false)
	{
		return $failure_return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save($key, $data, $ttl = null)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function test($key)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function flush()
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadata($key)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStats()
	{
		return array(
			'name' => 'Black Hole',
			'items' => 0,
			'hits' => 0,
			'misses' => 0,
			'servers' => array(),
		);
	}
}