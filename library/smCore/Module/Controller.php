<?php

/**
 * smCore Module Controller
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

abstract class Controller
{
	protected $_app;
	protected $module;

	/**
	 * 
	 *
	 * @param smCore\Module $parentModule
	 */
	public function __construct(Application $app, Module $module)
	{
		$this->_app = $app;
		$this->module = $module;
	}

	/**
	 * Run code before dispatching any method in this Controller.
	 */
	public function preDispatch()
	{
	}

	/**
	 * Run code after dispatching any method in this Controller.
	 */
	public function postDispatch()
	{
	}
}