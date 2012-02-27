<?php

/**
 * smCore platform
 *
 * @package smCore
 * @author Norv
 * @license MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * @version 1.0 alpha
 */

namespace smCore\handlers;
use smCore\model\Module;

/**
 * ModuleController: an abstract class for modules controllers to extend.
 */
abstract class ModuleController implements ActionController
{
	protected $_parentModule;
	protected $_context;

	/**
	 * Constructor takes the parent Module as parameter.
	 *
	 * @param \smCore\model\Module $parentModule
	 */
	public function __construct(Module $parentModule)
	{
		$this->_parentModule = $parentModule;
		$this->_context = array();
	}

	/**
	 * Return the parent Module.
	 *
	 * @return \smCore\model\Module
	 */
	public function getParentModule()
	{
		return $this->_parentModule;
	}

    /**
     * Set the parent module
     *
     * @param $module
     */
	public function setParentModule($module)
	{
		if (!empty($module) && $module instanceof Module)
			$this->_parentModule = $module;
	}
}