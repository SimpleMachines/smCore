<?php

/**
 * smCore Cache Factory
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

class Factory
{
	/**
	 * Load an installed cache driver by name
	 *
	 * @param string $driver  The name of the driver, i.e. "File"
	 * @param array  $options Options to pass to the driver's constructor
	 *
	 * @return \smCore\Cache\Driver\AbstractDriver
	 */
	public static function factory($driver, array $options = array())
	{
		$class = "\\smCore\\Cache\\Driver\\" . ucfirst(strtolower($driver));
		return new $class($options);
	}
}