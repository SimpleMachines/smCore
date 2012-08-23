<?php

/**
 * smCore Module Storage
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

namespace smCore\Module;

use smCore\Application, smCore\Module;

abstract class Storage extends smCore\Storage
{
	protected $_app;
	protected $module;

	public function __construct(Application $app, Module $module)
	{
		$this->_app = $app;
		$this->module = $module;
	}
}