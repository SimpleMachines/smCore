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

namespace smCore\Storage;

use smCore\Application, smCore\Exception;

class Factory
{
	protected $_storages = array();

	protected $_app;

	public function __construct(Application $app)
	{
		$this->_app = $app;
	}

	public function factory($name, $safe_mode = true)
	{
		if ($safe_mode && $this->_app['sending_output'] === true)
		{
			throw new Exception('Cannot load storages after output has been started.');
		}

		if (!empty($this->_storages[$name]))
		{
			return $this->_storages[$name];
		}

		$class = 'smCore\\Storage\\' . $name;
		return $this->_storages[$name] = new $class($this->_app);
	}
}