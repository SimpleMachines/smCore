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

use smCore\Module, smCore\Container;

abstract class Controller
{
	protected $_container;
	protected $_parent_module;

	/**
	 * 
	 *
	 * @param smCore\Module $parentModule
	 */
	public function __construct(Container $container, Module $parent_module)
	{
		$this->_container = $container;
		$this->_parent_module = $parent_module;
	}

	/**
	 * Get the parent module, in order to use its helper methods.
	 *
	 * @return smCore\Module The module which owns this controller
	 */
	protected function _getParentModule()
	{
		return $this->_parent_module;
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